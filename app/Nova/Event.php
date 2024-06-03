<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Actions\ExportAsCsv;
use App\Nova\Metrics\EventMaleCount;
use App\Nova\Metrics\EventFemaleCount;
use App\Nova\Metrics\ReservationsTrend;

class Event extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Event::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
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
            ID::make(),
            Text::make('Name')->sortable()->rules('required'),
            Select::make('Status')->sortable()
                ->rules('required')
                ->options(['in_progress' => 'In progress', 'published' => 'Published'])
                ->displayUsingLabels(),
            Number::make('Refund Percentage')->sortable()->rules('required')
                ->help('If the paid price was $160, then \'40\' will '
                    . 'mean that the customer gets a refund of $64. ' 
                    . 'When the customer requests a refund, it will '
                    . 'be calculated by taking the \'Reservation\' price '
                    . '* \'Event\' refund percentage / 100. Here\'s where it may go wrong - '
                    . 'the \'Reservation\' price might not actually match the value stored in '
                    . 'stripe. So, if the \'Reservation\' price is 16000, but stripe is 10000,'
                    . ' and refund is 40, then the customer will still recieve $64 dollar refund.'),
            DateTime::make('Start On')->sortable()->rules('required'),
            DateTime::make('End On')->sortable()->rules('required'),
            DateTime::make('Registration Start At')->sortable()->rules('required'),
            DateTime::make('Registration End At')->sortable()->rules('required'),
            DateTime::make('Refunds Available Until')->sortable()->rules('required'),
            BelongsToMany::make('Rooms')->fields(fn() => [Number::make('Price')
                ->help('Warning: This value is in pennies/cents NOT dollars.')]),
            HasMany::make('Reservations', 'reservations', Reservation::class),
            HasMany::make('Cots', 'cots', Cot::class),
            HasMany::make('Avaialable Cots', 'availableCots', Cot::class)
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
        return [
            EventMaleCount::make()
                ->onlyOnDetail()
                ->width('1/4'),
            EventFemaleCount::make()
                ->onlyOnDetail()
                ->width('1/4'),
            ReservationsTrend::make()
                ->onlyOnDetail()
                ->width('1/4'),
        ];
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
            ExportAsCsv::make()->nameable(),
            \App\Nova\Actions\ImportRooms::make(),
        ];
    }
}
