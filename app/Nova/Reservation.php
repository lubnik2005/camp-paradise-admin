<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Actions\ExportAsCsv;
use Laravel\Nova\Http\Requests\NovaRequest;

class Reservation extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Reservation::class;

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
        'first_name', 'last_name'
    ];

    /**
     * The visual style used for the table. Available options are 'tight' and 'default'.
     *
     * @var string
     */
    public static $tableStyle = 'tight';

    /**
     * The number of resources to show per page via relationships.
     *
     * @var int
     */
    public static $perPageViaRelationship = 20;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('First Name')->sortable()->rules('required'),
            Text::make('Last Name')->sortable()->rules('required'),
            Number::make('Price')->sortable(),
            URL::make('Stripe', fn () => $this->stripe_payment_intent ? env('STRIPE_DASHBOARD_URL', 'https://dashboard.stripe.com/') . 'payments/' . $this->stripe_payment_intent : '')
                ->displayUsing(fn () => $this->stripe_payment_intent ? env('STRIPE_DASHBOARD_URL', 'https://dashboard.stripe.com/') . 'payments/' . $this->stripe_payment_intent : '')
                ->sortable(),
            BelongsTo::make('Attendee Account', 'attendee', Attendee::class)->sortable()->rules('required')->searchable(),
            BelongsTo::make('Event')->sortable()->rules('required'),
            BelongsTo::make('Room')->sortable()->rules('required'),
            BelongsTo::make('Cot')->sortable()->rules('required'),
            DateTime::make('Updated At')->exceptOnForms(),
            DateTime::make('Created At')->exceptOnForms(),
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
        return [
            ExportAsCsv::make()->nameable()->withFormat(function (\App\Models\Reservation $model) {
                return [
                    'Attendee First Name' => $model->attendee()->first()->first_name,
                    'Attendee Last Name' => $model->attendee()->first()->last_name,
                    'Attendee Email Address' => $model->attendee()->first()->email,
                    'Reservation First Name' => $model->first_name,
                    'Reservation Last Name' => $model->last_name,
                    'Event Name' => $model->event()->first()->name,
                    'Room Name' => $model->room()->first()->name,
                    'Cot Description' => $model->cot()->first()->description,
                    'Created At' => $model->created_at,
                    'Updated At' => $model->updated_at,
                    'Price' => $model->price
                ];
            })
        ];
    }
}
