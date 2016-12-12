<!DOCTYPE html>
<html>
@include('report_template.excel.style')

<table>
    <tr class="title-row">
        <td colspan="12">
            <b>@lang('report.template.customer.report_name')</b>
        </td>
    </tr>
    <tr>
        <td colspan="12">&nbsp;</td>
    </tr>
    @if($showParameter)
        @if(!empty($customerName))
            <tr>
                <td colspan="12">
                    @lang('report.template.customer.parameter.name') {{ ': ' . $customerName }}
                </td>
            </tr>
        @endif
        @if(!empty($profileName))
            <tr>
                <td colspan="12">
                    @lang('report.template.customer.parameter.profile_name') {{ ': ' . $profileName }}
                </td>
            </tr>
        @endif
        @if(!empty($bankAccount))
            <tr>
                <td colspan="11">
                    @lang('report.template.customer.parameter.bank_account') {{ ': ' . $bankAccount }}
                </td>
            </tr>
        @endif
    @endif
    <tr>
        <td colspan="11">&nbsp;</td>
    </tr>
    <tr class="header-row">
        <th>@lang('report.template.customer.header.store')</th>
        <th>@lang('report.template.customer.header.sign_code')</th>
        <th>@lang('report.template.customer.header.name')</th>
        <th>@lang('report.template.customer.header.address')</th>
        <th>@lang('report.template.customer.header.city')</th>
        <th>@lang('report.template.customer.header.phone_number')</th>
        <th>@lang('report.template.customer.header.fax_num')</th>
        <th>@lang('report.template.customer.header.tax_id')</th>
        <th>@lang('report.template.customer.header.payment_due_day')</th>
        <th>@lang('report.template.customer.header.price_level')</th>
        <th>@lang('report.template.customer.header.status')</th>
        <th>@lang('report.template.customer.header.remarks')</th>
    </tr>
    @foreach($customers as $key => $customer)
        <tr class="data-row">
            <td>{{ $customer->store->name }}</td>
            <td>{{ $customer->sign_code }}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->address }}</td>
            <td>{{ $customer->city }}</td>
            <td>{{ $customer->phone_number }}</td>
            <td>{{ $customer->fax_num }}
            <td>{{ $customer->tax_id }}</td>
            <td class="text-center">{{ $customer->payment_due_day }}</td>
            <td class="text-center">{{ $customer->priceLevel->name }}</td>
            <td class="text-center">{{ $statusDDL[$customer->status] }}</td>
            <td>{{ $customer->remarks }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="11">&nbsp;</td>
    </tr>
    <tr class="footer-row">
        <td colspan="11">
            @lang('report.template.customer.footer', ['user' => $currentUser, 'date' => $reportDate])
        </td>
    </tr>
</table>
</html>