<?php

Route::get('api/captcha', function() {
    return \CRSCompany\FrameworC\Components\Form::onCaptcha();
});
