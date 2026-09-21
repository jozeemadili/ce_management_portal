<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\Notifications\SMSController;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\InsurerIntermediary;
use App\Models\PRODUCT;
use App\Models\PRODUCTCONDITION;
use App\Models\User;
use App\TIRAClient\Scripts\Classes\Utils;
use App\TIRAClient\Scripts\Classes\TIRAClient;
use App\TIRAClient\Scripts\Classes\EsbClient;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; 


class PortalUsersController extends Controller
{
    public function get()
    {
        if(Auth::user()->company_id == 1)
        {
            $users = User::orderBy('id','desc')->paginate(10);
        }else
        {
            $users = User::where('company_id',Auth::user()->company_id)->orderBy('id','desc')->paginate(10);
        }
        
        $list_companies = Company::orderBy('id','ASC')->get();
        if(substr(Route::getCurrentRoute()->uri,3) == "security/users")
        {
            return view('admin.security.users',['users' => $users,'companies'=>$list_companies]);
        }
        return $users;
    }

    public function manageProfile(Request $request)
    {
        $user = User::find($request->id);
        if($user == null)
        {
            abort(404);
        }
        return"";
    }

    public function getEmployees()
    {
        $users = User::orderBy('id','desc')->paginate(10);
      
           return view('admin.employees.employees-registration',['users' => $users]);
   
        
    }
    
    public function updateProdyctStatus(Request $request)
    {
        $endPoint='http://172.16.3.198:30002/api/v2/hmcis/'.$request->status.'/'.$request->id.'';
        $body='';
            $response = EsbClient::SendesbRequest($body,$endPoint);
            $myResponse = json_encode($response);
            $responseArray = json_decode($myResponse, true);
            if ($responseArray && isset($responseArray['status_code']) && $responseArray['status_code'] === 201) 
                {
                    return to_route('products-condtions',['id' => $request->id])->with('success', 'Successfully ');
                }
            else
                {
                    return to_route('products-condtions',['id' => $request->id])->with('success', 'Failed');
                }
    }

   

    public function profile()
    {
        return view('admin.security.profile');
    }

    public function change_password(Request $request)
    {
        $request->validate(['new_password'      => ['min:8','regex:/[a-z]/', 'regex:/[A-Z]/','regex:/[@$!%*#?&]/']]);
        $User = User::find(Auth::user()->id);
        $User->password    = Hash::make($request->new_password);
        $User->is_first_time_pin = false;
        $User->save();
        Session::flush();
        Auth::logout();
        return redirect('/')->with('success', 'Successfully changed password, Login again');
    }

    public function resert_password(Request $request)
    {
        $User = User::find($request->id);
        $User->password    = Hash::make("Azania@2024!");
        $User->is_first_time_pin = false;
        $User->save();
        
        return redirect('/v1/security/users')->with('success', 'Successfully Resert password');
    }
    public function sendOTP($otp, $authenticatedUser = null)
    {
        $utils = new Utils();
        $user = $authenticatedUser == null ? Auth::user() : $authenticatedUser;
            
        $message       = "Dear ".ucfirst($user->first_name).", Use OTP ".$otp." to Verify your phone number";
        $SMSController = new SMSController();
        try{
             $response = $SMSController->send("255".$user->mobile, $message);
            //$response = $SMSController->sendSandBox("255".$user->mobile, $message);
            return array('responseCode' => 'SUCCESS', 'message' => 'SMS Submitted', 'response' => $response);
        }
        catch(Exception $e){
            return array("responseCode" => "FAILED", "message" => $e->getMessage());
        } 
    }

    public function search(Request $request)
    { 
        $company_id = Auth::user()->company_id;
        $accessAll  = Auth::user()->company_id == 1;
        $users = $accessAll ? User::where('mobile', $request->phone_number)->orWhere('id_number', $request->id_number)->orWhere('first_name', $request->cname)->orWhere('middle_name', $request->cname)->orWhere('last_name', $request->cname)->orderBy('id','desc')->paginate(10) : User::where('company_id', $company_id)->where('mobile', $request->phone_number)->orWhere('id_number', $request->id_number)->orWhere('first_name', $request->cname)->orWhere('middle_name', $request->cname)->orWhere('last_name', $request->cname)->orderBy('id','desc')->paginate(10);

        if(substr(Route::getCurrentRoute()->uri,3) == "security/users")
        {
            return view('admin.security.users',['users' => $users]);
        }
        return response()->json(['responseCode'=> 'SUCCESS','message'=>'User Found Successfully', 'users' => $users]);
    }

    public function register(Request $request)
    {
        $request->validate(['first_name' => ['required', 'min:3'], 'email' => ['required', 'email','unique:users'],'password' => ['required','min:8','regex:/[a-z]/', 'regex:/[A-Z]/','regex:/[@$!%*#?&]/'], 'mobile' => ['required', 'min:9', 'max:9', 'unique:users']]);
        if(Auth::user()->company_id == 1)
        {
            $company_id=$request->company_id;
        }else
        {
            $company_id=Auth::user()->company_id;
        }           
        $user = User::create(
            [
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'emp_id'        => $request->emp_id,
                'branch_id'     => $request->branch_id,
                'title'         => $request->title,
                'first_name'    => $request->first_name,
                'middle_name'   => $request->middle_name,
                'last_name'     => $request->last_name,
                'type'          => $request->type,
                'mobile'        => $request->mobile,
                'id_type'       => $request->id_type,
                'id_number'     => $request->id_number,
                'dob'           => $request->dob,
                'role'          => $request->role,
                'company_id'    => intval($company_id),
                'created_by'    => intval(Auth::user()->id),
            ]);
            return redirect()->route('portal-users')->with('success', 'e-Mkopo User <b>'.strtoupper($request->first_name).' With user name ('.strtoupper($request->username).')</b> Successfully Registered  : ');
    }
    public function loginWeb(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'],'password' => ['required']]);
        
        if (!Auth::attempt($credentials))
        {
            return redirect('/')->with('error', 'Invalid username or Password');
        }
        else
        {
            $user = User::where('email', $request['email'])->first();
                if($user->status != 'Active')
                {
                    return redirect('/')->with('error', 'Sorry ! You have been deactivated.');
                }
                else
                {
                    if($request->password ==="Safe@2024!")
                    {
                        return redirect()->intended(route('security-user-profile'));
                    }
                    else
                    {
                        return redirect()->intended(route('home'));
                    }

                }

            
            
        }
        
   }
    public function logout(Request $request)
    {
        try
        {
            if(substr(Route::getCurrentRoute()->uri, 3) == "logout")
            {
                Session::flush();
                Auth::logout();
                return redirect('/')->with('error', 'Successfully Logged out');
            }

            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'responseCode'  => 'SUCCESS',
                'message'       => 'Logged Out Successfully',
              ]);
        }
        catch(Exception $e)
        {
            return response()->json([
                'message'       => $e->getMessage(),
                'responseCode'  => 'LOGGED_OUT_EXCEPTION'
              ]);
        }
    }

    public static function resolveIdType($id_type)
    {
        return ($id_type == 1 ? "NIDA" : (($id_type == 2 ? "Voters" : ($id_type == 3 ? "PASSPORT" : ($id_type == 4 ? "DRIVING LICENCE" : ($id_type == 5 ? "ZANID" : ($id_type == 6 ? "TIN" : ($id_type == 7 ? "INCORPORATION CERTIFICATE" : "N/A"))))) )));
    }

    public function howToUse(Request $request)
    {
        return view('guest.how-to-use');
    }

    public function index(Request $request)
    {
        return view('admin.authentication.login');
    }

}
