<div>
    <h6>{{ $currentAction }}</h6>

    <div style="display : {{ $currentAction == 'Password Reset' ? '' : 'none' }}">
    <div class="form-group">
        <label>Enter Your Mobile Number</label>
        <div class="row">
            <div class="col-3">
                <input class="form-control" disabled type="text" value="+255">
            </div>
            <div class="col-9">
                <input wire:model="mobile" class="form-control" type="number" min="0" maxlength="999999999" placeholder="Phone Number ...">
            </div>
        </div>
    </div>
    <div class="form-group"><button {{ strlen($mobile) == 9 ? '' : 'disabled' }} wire:click="resetPassword" style="width:100%" class="btn btn-outline-primary btn-block">Reset Password</button></div>
    </div>


    <div style="display : {{ $currentAction == 'Verify OTP' ? '' : 'none' }}">
        <div class="form-group">
            <label>Enter OTP Sent to +255{{ $mobile }}</label>
            <input wire:model="otp" class="form-control" type="number" min="0" maxlength="999999" placeholder="OTP ...">
        </div>
        <div class="form-group"><button {{ strlen($otp) == 6 ? '' : 'disabled' }} wire:click="verifyOTP" style="width:100%" class="btn btn-outline-primary btn-block">Verify Otp</button></div>
    </div>


    <div style="display : {{ $currentAction == 'Change Password' ? '' : 'none' }}">
        <div class="form-group">
            <label>New Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-lock"></i></span>
                <input wire:model="new_password" class="form-control" type="text" placeholder="New Password ...">
                <div class="show-hide"><span class=""></span></div>
            </div>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="icon-lock"></i></span>
                <input wire:model="new_password_confirm" class="form-control" type="text" placeholder="Confirm Password ...">
                <div class="show-hide"><span class=""></span></div>
            </div>
            @if($password_strength != null)
            <br />
            <div class="alert alert-danger outline alert-dismissible fade show" role="alert">
                <i class="icon-info-alt txt-danger"></i>
                    {{ $password_strength }}
           </div>
            @endif
        </div>
        <div class="form-group"><button {{ strlen($new_password_confirm) > 8 &&  $new_password == $new_password_confirm  && $password_strength == null ? '' : 'disabled' }} wire:click="changePassword"  style="width:100%" class="btn btn-outline-primary btn-block">Change Password</button></div>
    </div>

    <center>
    <div wire:loading.delay>
        <div class="loader-box">
            <div class="loader-7" style="width: 50px; height:50px;"></div>
        </div>
        <h5 class="f-w-100">Processing ...</h5>
    </div>
    </center>

</div>
