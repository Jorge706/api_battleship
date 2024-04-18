<div style="width: 100%; height: 100%; background-color: rgb(243, 247, 252); font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;">
    <div style="text-align: center; width: 100%;"><img src="{{ $message->embed(public_path().$pathToImage) }}" alt="" role="presentation" style="margin-top: 15px; margin-bottom: 5px;"></div>
    <div style="text-align: center; color: black; font-size: 20px; font-weight: 300; word-wrap: break-word; margin-bottom: 20px;">{{ __('email.activate_welcome', ['name' => $name]) }}</div>
    <div style="text-align: center; color: black; font-size: 16px; font-weight: 300; word-wrap: break-word; margin-bottom: 10px;">{{ __('email.activate_introduction') }}<br/>{{ __('email.activate_body') }}</div>
    <div style="text-align: center; color: black; font-size: 17px; font-weight: 300; word-wrap: break-word; margin-bottom: 10px;">{{ $activateUrl }}</div>
    <div style="text-align: center; color: black; font-size: 16px; font-weight: 300; word-wrap: break-word; margin-bottom: 5px;">{{ __('email.activate_conclusion') }}</div>
    <div style="text-align: center; color: black; font-size: 12px; font-weight: 300; word-wrap: break-word; margin-bottom: 15px; font-style: italic;">{{ __('email.activate_signature') }}</div>
</div>