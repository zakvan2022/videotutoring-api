<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

use App\Classes\PropertySuccessResponse;
use App\Classes\SuccessResponse;
use App\Classes\PropertyErrorResponse;
use App\Classes\ErrorResponse;

use App\Models\Price;
use App\Models\Topic;
use App\Models\Minor;
use App\Models\Address;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use App\Models\Degree;
use App\Models\BasicDegree;
use App\Models\Identity;
use App\Models\CreditCardInfo;
use App\Models\PaypalInfo;
use App\Models\PaypalWithdrawInfo;

use App\Repositories\PaymentRepository;
use App\Services\StripeService;
use App\Services\PaypalService;

use Exception;

class PaymentController extends Controller
{

    public function __construct() {
        StripeService::setApiKey();
        PaypalService::setToken();
        $this->paypal_provider = new PaypalService();
    }
    /** GET
    * /api/payment/stripe
    * @return \Illuminate\Http\Response 
    */
    public function getStripeInfo(Request $request) {
        $user = Auth::user();
        if (!$user->credit_card_info)
            return response()->json(new ErrorResponse("This account has no stripe card", "400", []), $this->badRequest);
        $customer = StripeService::getCustomerInfo($user->credit_card_info->stripe_customer_id);
        if (!$customer)
            return response()->json(new ErrorResponse("Your card has something wrong, please try to connect again", "400", []), $this->badRequest);
        $result['brand'] = $customer->sources->data[0]->brand;
        $result['cvc_check'] = $customer->sources->data[0]->cvc_check;
        $result['exp_month'] = $customer->sources->data[0]->exp_month;
        $result['exp_year'] = $customer->sources->data[0]->exp_year;
        $result['last4'] = $customer->sources->data[0]->last4;
        $result['address_zip'] = $customer->sources->data[0]->address_zip;
        return response()->json($result, $this->successStatus);
    }

    /** POST
    * /api/payment/stripe/create
    * @return \Illuminate\Http\Response 
    */
    public function createStripeCustomer(Request $request) {
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
  
        if ($user->credit_card_info)
            return response()->json(new ErrorResponse("Your card has been already connected to this account", "400", []), $this->badRequest);

        try {
            $customer = StripeService::createCustomer($user->email, $input['stripeToken']);
        } catch (Exception $e) {
            return response()->json(new ErrorResponse($e->getMessage(), "400", []), $this->badRequest);
        }

        $credit_card = new CreditCardInfo();
        $credit_card->fill([
            'user_id' => $user->id,
            'stripe_customer_id' => $customer->id
        ]);
        $credit_card->save();
        $user->default_billing_type = 'credit_card';
        $user->save();
        return response()->json(new SuccessResponse("Your card was connected to your account successfully", "200", []), $this->successStatus);
    }

    /** DELETE
     * /api/payment/stripe/remove
     * @return \Illuminate\Http\Response
     */
    public function removeStripeCustomer(Request $request) {
        $user = Auth::user();
        if (!$user->credit_card_info)
            return response()->json(new ErrorResponse("This account has no stripe card", "400", []), $this->badRequest);

        try {
            StripeService::removeCustomer($user->credit_card_info->stripe_customer_id);
        } catch (Exception $e) {
            return response()->json(new ErrorResponse($e->getMessage(), "400", []), $this->badRequest);
        }

        $credit_card = CreditCardInfo::where('user_id', $user->id);
        $credit_card->delete();
        return response()->json(new SuccessResponse("Your card was removed successfully", "200", []), $this->successStatus);
    }

    /** POST
     * /api/payment/stripe/charge
     * @return \Illuminate\Http\Response
     */
    public function chargeStripePayment(Request $request) {
        $user = Auth::user();
        if (!$user->credit_card_info)
            return response()->json(new ErrorResponse("This account has no stripe card", "400", []), $this->badRequest);
        $customer_id = $user->credit_card_info->stripe_customer_id;
        
        try {
            StripeService::charge($customer_id, 1000, 'usd', 'This is tutoring payment!');
            PaymentRepository::addTransactionHistory($user->id, "Charged Via Stripe", 700, "Script Payment Test");
            PaymentRepository::updateBalance($user->id, 300);
        } catch (Exception $e) {
            return response()->json(new ErrorResponse($e->getMessage(), "400", []), $this->badRequest);
        }
        
        return response()->json(new SuccessResponse("$1000 is paied successfully", "200", []), $this->successStatus);
    }

    public function chargePaypalPayment(Request $request) {
        $user = Auth::user();
        if (!$user->paypal_info)
            return response()->json(new ErrorResponse("This account has no paypal", "400", []), $this->badRequest);
        $payerID = $user->paypal_info->payer_id;
    }

    public function setupPaypal (Request $request) {
        $user = Auth::user();
        $input = json_decode($request->getContent(), true);
        $paypal_withdraw_info = $user->paypal_withdraw_info;
        if (!$paypal_withdraw_info)
            $paypal_withdraw_info = new PaypalWithdrawInfo(['user_id' => $user->id]);
        $paypal_withdraw_info->fill($input);
        $paypal_withdraw_info->save();
        $user->default_payment_type = 'paypal';
        $user->save();
        return response()->json(new SuccessResponse("Paypal is setup successfully", "200", []), $this->successStatus);
    }

    public function requestPreApproval(Request $request) {
        $user = Auth::user();
        $input = json_decode($request->getContent(), true);

        $dateFormat="Y-m-d\Th:i:s.000\Z";
        $nowTime = date($dateFormat);
        $senderEmail = $input['email'];
        $startingDate = date($dateFormat,strtotime('+3 minutes',strtotime($nowTime)));
        $endingDate = date($dateFormat,strtotime('+1 year',strtotime($nowTime)));

        try {
            $preapprovalKey = $this->paypal_provider->requestPreApproval(
                $senderEmail,
                $startingDate,
                $endingDate
            );
            $paypal_info = $user->paypal_info;
            if (!$paypal_info) $paypal_info = new PaypalInfo(['user_id'=>$user->id]);
            $input['preapproval_id'] = $preapprovalKey;
            $input['active'] = false;
            $paypal_info->fill($input);
            $paypal_info->save();
        } catch (Exception $e) {
            return response()->json(new ErrorResponse($e->getMessage(), "400", []), $this->badRequest);
        }
            
        $result['return_url'] = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-preapproval&preapprovalkey=".$preapprovalKey;
        return response()->json($result, $this->successStatus);
    }

    public function activePreApproval(Request $request) {
        $user = Auth::user();
        $paypal_info = $user->paypal_info;
        if (!$paypal_info)
            return response()->json(new ErrorResponse('You must request preapproval first', '400', []), $this->badRequest); 
        $paypal_info->active = true;
        $paypal_info->save();
        $user->default_billing_type = 'paypal';
        $user->save();
        return response()->json($paypal_info, $this->successStatus);
    }

    public function withdrawToPayPal(Request $request) {
        $user = Auth::user();

        if (!$user->paypal_withdraw_info)
            return response()->json(new ErrorResponse("This account is not connected to paypal", "400", []), $this->badRequest);
        if (!$user->default_payment_type || $user->default_payment_type!=='paypal')
            return response()->json(new ErrorResponse("You must set paypal as default", "400", []), $this->badRequest);
        if (!$user->balance || $user->balance->amount < 1)
            return response()->json(new ErrorResponse("Balance is insufficient", "400", []), $this->badRequest);
        
        $email = $user->paypal_withdraw_info->email;
        $amount = $user->balance->amount;
        $description = "You got payment from VideoTutoring Team";
        try {
            $result = $this->paypal_provider->withdraw($email, $amount, $description);
            PaymentRepository::addTransactionHistory($user->id, "Withdraw via Paypal", $amount, $description);
            PaymentRepository::updateBalance($user->id, -$amount);
        } catch (Exception $e) {
            return response()->json(new ErrorResponse($e->getMessage(), "400", []), $this->badRequest);
        }
        return response()->json(new SuccessResponse("Payment was withdrew via paypal", "200", []), $this->successStatus);
    }

    public function test(Request $request) {
        $preapprovalKey = "PA-84730898TW892311M";
        $senderEmail = "sb-ikuqr229477@personal.example.com";
        $receiverEmail = "sb-hxvlp232243@business.example.com";
        try {
            $response = $this->paypal_provider->executeAdaptivePay($senderEmail, $receiverEmail, $preapprovalKey, '350');
        } catch (Exception $e) {
            return response()->json(new ErrorResponse($e->getMessage(), "400", []), $this->badRequest);
        }
        return response()->json($response, $this->successStatus);
    }
}