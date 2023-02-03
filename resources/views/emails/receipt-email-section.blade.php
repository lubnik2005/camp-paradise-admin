@extends('emails/base-email')

@section('title')
    Hi {{ $attendee->first_name }} {{ $attendee->last_name }}, <br />
    Thank you for your order!
@endsection

@section('content')
    We're looking forward to seeing you at camp {{ $event->name }}!<br />
    Your order was successfully placed and your payment has been processed.
@endsection

@section('content-secondary')
    <!--[if mso | IE]>
                                                                                                                                      </td>
                                                                                                                                <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600">
                                                                                                                                    <tr>
                                                                                                                                        <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                                                                                                                                            <![endif]-->
    <div
        style="background: #ffffff; background-color: #ffffff; margin: 0px auto; border-radius: 20px; max-width: 600px;margin-top:30px">
        <div class="mj-column-per-100 mj-outlook-group-fix"
            style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;"
                width="100%">
                <tr>
                    <td align="left" class="receipt-table" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <table cellpadding="0" cellspacing="0" width="100%" border="0"
                            style="color:#8189A9;font-family:Montserrat, Helvetica, Arial, sans-serif;font-size:13px;line-height:22px;table-layout:auto;width:100%;border:none;">
                            <tr>
                                <th colspan="3"
                                    style="font-size: 20px; line-height: 30px; font-weight: 500; text-align: center; border-bottom: 2px solid #ddd; padding: 0 0 20px 0;"
                                    align="center">Order summary </th>
                            </tr>
                            <tr>
                                <td style="font-size: 15px; line-height: 22px; font-weight: 400; word-break: normal; width: 45%; padding-top: 10px;"
                                    width="60%">{{ $reservation->room->name }} {{ $reservation->cot->description }}</td>
                                <td style="font-size: 15px; line-height: 22px; font-weight: 400; word-break: normal; text-align: right; width: 45%;"
                                    width="20%" align="right">
                                    <p>{{ substr($event->start_on, 0, 10) }} - {{ substr($event->end_on, 0, 10) }}</p>
                                </td>
                                <td style="font-size: 15px; line-height: 22px; font-weight: 400; word-break: normal; text-align: right; width: 10%;"
                                    width="20%" align="right">${{ $reservation->price / 100 }}</td>
                            </tr>
                            <tr>
                                <td style="word-break: normal; font-size: 20px; line-height: 30px; border-top: 1px solid #ddd; font-weight: 500; padding: 10px 0px 0px 0px; text-align: left;"
                                    colspan="2" align="left">Total</td>
                                <td style="word-break: normal; font-size: 20px; line-height: 30px; border-top: 1px solid #ddd; font-weight: 500; text-align: right; padding: 10px 0px 0px 0px;"
                                    align="right">${{ $reservation->price / 100 }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <!--[if mso | IE]>
                                                                                                                                        </td>
                                                                                                                                    </tr>
                                                                                                                                </table>
@endsection
