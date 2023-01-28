<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Fields\PasswordConfirmation;
use Laravel\Nova\Http\Requests\NovaRequest;

class Attendee extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Attendee::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'email';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'email',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make('Email')->sortable()->rules('required'),
            Text::make('First Name')->sortable()->rules('required'),
            Text::make('Last Name')->sortable()->rules('required'),
            URL::make('Stripe', fn () => $this->stripe_id ? env('STRIPE_DASHBOARD_URL', 'https://dashboard.stripe.com/') . 'customers/' . $this->stripe_id : '')
                ->displayUsing(fn () => $this->stripe_id ? env('STRIPE_DASHBOARD_URL', 'https://dashboard.stripe.com/') . 'customers/' . $this->stripe_id : '')
                ->sortable(),
            DateTime::make('Email Verified At')->sortable(),
            Text::make('Church')->sortable()->rules('required'),
            Select::make('Sex')->sortable()->options(['m' => 'Male', 'f' => 'Female'])->rules('required'),
            Password::make('Password')->onlyOnForms(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
