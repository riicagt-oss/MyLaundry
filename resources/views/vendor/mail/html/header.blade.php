@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('app_icon.png') }}" class="logo" alt="{{ $slot }}" style="height: 75px; width: 75px; object-fit: contain;">
</a>
</td>
</tr>
