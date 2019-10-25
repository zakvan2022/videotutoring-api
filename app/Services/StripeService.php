<?php

namespace App\Services;

use Illuminate\Http\Request;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;

use Exception;

class StripeService
{

    /**
     * set ApiKey
     */
    public static function setApiKey() {
        Stripe::setApiKey(env("STRIPE_SECRET", "sk_test_nuxlrNxasR6tYCltMHy1J5Vo00wbzPBPzn"));
    }
    
    /**
     * get customer information
     * @param {string} customerId
     * @return {customer object} 
     */
    public static function getCustomerInfo($customerId) {
        return Customer::retrieve($customerId);
    }

    /**
     * create stripe custoer 
     * @param {string} email
     * @param {string} stripeToken
     * @return {object} customer
     */
    public static function createCustomer($email, $stripeToken) {
        try {
            $customer = Customer::create(array(
                'email' => $email,
                'source' => $stripeToken,
            ));
            return $customer;
        } catch(\Stripe\Exception\CardException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\RateLimitException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\AuthenticationException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * remove stripe customer
     * @param {string} customerId 
     */
    public static function removeCustomer($customerId) {
        try {
            $customer = Customer::retrieve($user->credit_card_info->stripe_customer_id);
            $customer->delete();
        } catch(\Stripe\Exception\CardException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\RateLimitException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\AuthenticationException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param {string} customerId
     * @param {integer} amount
     * @param {string} currency
     * @param {string} description
     */
    public static function charge($customerId, $amount, $currency='usd', $description='') {
        try {
            $charge = Charge::create(array(
                'customer' => $customerId,
                'amount'   => $amount * 100,
                'currency' => $currency,
                "description" => $description
            ));
        } catch(\Stripe\Exception\CardException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\RateLimitException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\AuthenticationException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            throw new Exception($e->getMessage());
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}