<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\DateTime;

use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

class TutorProfile extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\TutorProfile';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Number::make("level")->sortable(),
            Number::make("rating")->sortable(),
            Number::make("Classes Passed")->sortable(),
            Number::make("Total Time")->sortable(),
            Select::make("Availability")->options(
                [
                    "available" => "Available",
                    "notavailable" => "Not Available"
                ]
            ),
            Text::make("Payment Type")->sortable()->help("Accept paypal, credit_card, code"),
            Text::make("Payment Id")->sortable(),
            Text::make("Billing Type")->sortable()->help("Accept paypal, credit_card, code"),
            Text::make("Billing Id")->sortable(),
            BelongsTo::make("User"),
            BelongsTo::make("Identity"),
            BelongsTo::make("W9Form"),
            BelongsToMany::make("Prices"),
            BelongsToMany::make("Topics"),
            HasMany::make("Degrees"),
            HasMany::make("Video Classes"),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
