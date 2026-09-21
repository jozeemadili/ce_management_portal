<?php

namespace App\Http\Livewire\Components\Auth;

use App\Http\Controllers\API\Auth\PortalUsersController;
use App\Models\User;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Resetpassword extends Component
{
    public $currentAction = "Password Reset";
    public $mobile;
    public $otp;
    public $new_password;
    public $new_password_confirm;
    public $user;
    public $password_strength = null;

    public $true_otp;

    public function render()
    {
        return view('livewire.components.auth.resetpassword');
    }

    public function resetPassword()
    {
        try 
        {
            $this->true_otp = substr((mt_rand(1000000, 9999999)), 0, 6);
            $this->user = User::where('mobile', $this->mobile)->first();

            if($this->user != null)
            {
                $PortalUsersController = new PortalUsersController();
                $sendOTP = $PortalUsersController->sendOTP($this->true_otp, $this->user);
                if($sendOTP['responseCode'] == "SUCCESS")
                {
                     $this->currentAction = "Verify OTP";
                }
                else 
                {
                    $this->dispatchBrowserEvent('swal:modal', [
                        'type'    => 'error',  
                        'message' => 'Failed!', 
                        'text'    => $sendOTP['message']
                    ]);
                }
            }
            else 
            {
                $this->dispatchBrowserEvent('swal:modal', [
                    'type'    => 'error',  
                    'message' => 'Invalid!', 
                    'text'    => 'No User for +255'.$this->mobile
                ]);
            }
        }
        catch(Exception $e)
        {
            $this->dispatchBrowserEvent('swal:modal', [
                'type'    => 'error',  
                'message' => 'Failed!', 
                'text'    => $e->getMessage()
            ]);
        }
    }

    public function verifyOTP()
    {
        if($this->true_otp == $this->otp)
        {
            $this->currentAction = "Change Password";
        }
        else 
        {
            $this->dispatchBrowserEvent('swal:modal', [
                'type'    => 'error',  
                'message' => 'Invalid!', 
                'text'    => 'Invalid OTP provided'
            ]);
        }
    }

    public function updatedNewpassword()
    {
        $this->validatePasswordStrength($this->new_password);
    }

    public function updatedNewpasswordconfirm()
    {
        $this->validatePasswordStrength($this->new_password_confirm);
    }

    function validatePasswordStrength($password)
    {
        $this->password_strength = null;
        
        $length = strlen($password);
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasNumber = preg_match('/\d/', $password);
        $hasSymbol = preg_match('/[^a-zA-Z\d]/', $password);

        if($this->new_password != $this->new_password_confirm){
            $this->password_strength = 'Passwords do not Match';
        }

        if ($length < 8) {
            $this->password_strength = 'Password must be at least 8 characters long';
        } elseif (!$hasUppercase || !$hasLowercase || !$hasNumber || !$hasSymbol) {
            $this->password_strength = 'Must contain at least one uppercase letter, one lowercase letter, one number, and one symbol';
        } else {
            return null;
        }
    }

    public function changePassword()
    {
        $User = User::find($this->user->id);
        $User->password    = Hash::make($this->new_password_confirm);
        $User->is_first_time_pin = false;
        $User->save();
        Session::flush();
        Auth::logout();
        return redirect('/')->with('success', 'Successfully changed password, Login again');
    }
}
