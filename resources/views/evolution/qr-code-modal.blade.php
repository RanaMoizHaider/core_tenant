
@if(!$qrCode)
<div class="flex justify-center">
    <p class="text-xl font-bold text-center text-black">QR Code not found, please reconnect the instance</p>
</div>
@endif

@if($qrCode)
<div class="mb-4 text-center">
    <p class="text-xl font-bold text-black">Scan the QR Code in your WhatsApp to activate the Instance</p>
</div>

<div class="flex justify-center">
    <img src="{{ $qrCode }}" alt="QR Code" class="object-contain w-30 h-30">
</div>
@endif



