<?php

namespace App\Http\Controllers\API\Companies;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


class CompaniesController extends Controller
{
    public function get()
    {
        $companies = Company::orderBy('id', 'desc')->paginate(10);
            return view('admin.companies.registration',['companies' =>$companies]);
    }

    public function profile($id)
    {
        $company = Company::find($id);
        if($company != null)
        {
            return view('admin.companies.profile',['company' =>$company]);
        }
        else 
        {
            abort(404);
        }
    }
    public function updateCompanyStatus(Request $request)
    {
        $company = company::find($request->id);

        if($company == null)
        {
            abort(404);
        }

        $company->status = $request->status;


        $company->save();
       
       return redirect('v1/companies/registration');
    }
    public function profileUpdate($id, Request $request)
    {
        $company = Company::find($id);
        if(!$company)
        {
            abort(404);
        }

        $request->validate(
            [
                'name'                   => ['min:3', 'unique:companies'],
                'registration_number'    => ['min:5', 'unique:companies'],
                'short_form'             => ['min:3','max:5', 'unique:companies'],
                'license_number'         => ['min:5', 'unique:companies'],
                'tin'                    => ['min:6', 'unique:companies'],
                'code'                   => ['min:3', 'unique:companies'],
                'sale_point_code'        => ['min:3', 'unique:companies'],
                'category'               => ['min:3'],
                'email_address'          => ['email'],
                'phone_number'           => ['min:9', 'max:9', 'unique:companies'],
                'contact_person'         => ['min:3'],
                'status'                 => ['in:Active,Inactive,Pending,Rejected']
            ]);

        $company->name = ($request->name != null) ? $request->name : $company->name;
        $company->registration_number = ($request->registration_number != null) ? $request->registration_number : $company->registration_number;
        $company->short_form = ($request->short_form != null) ? $request->short_form : $company->short_form;
        $company->license_number = ($request->license_number != null) ? $request->license_number :  $company->license_number;
        $company->tin = ($request->tin != null) ? $request->tin : $company->tin;
        $company->code = ($request->code != null) ? $request->code : $company->code;
        $company->sale_point_code = ($request->sale_point_code !=null) ?  $request->sale_point_code :  $company->sale_point_code;
        $company->category = ($request->category != null) ? $request->category : $company->category;
        $company->logo = ($request->logo != null) ? $request->logo : $company->logo;
        $company->email_address = ($request->email_address != null) ? $request->email_address : $company->email_address;
        $company->postal_address = ($request->postal_address != null) ? $request->postal_address : $company->postal_address;
        $company->phone_number = ($request->phone_number != null) ? $request->phone_number : $company->phone_number;
        $company->contact_person = ($request->contact_person != null) ? $request->contact_person : $company->contact_person;
        $company->status = ($request->status != null) ? $request->status : $company->status;
        $company->color = ($request->color != null) ? $request->color : $company->color;
        $company->created_by = intval(Auth::user()->id);

        $company->save();
        return redirect()->route('companies-profile', ['id' => $company->id])->with('success',  'Company Updated Successfully');
    }

   

   

    public function branches()
    {
        $accessAll  =  (Auth::user()->roel == 'ADMIN');
        $branches = $accessAll ? Branch::orderBy('id','desc')->paginate(10) : Branch::where('company_id', Auth::user()->company_id)->orderBy('id','desc')->paginate(10);
        return view('admin.companies.branches', ['branches' => $branches]);
    }

    public function search(Request $request)
    {
        return Company::where('registration_number', $request->registration_number)->orWhere('tin', $request->tin)->orWhere('category', $request->category)->orderBy('id', 'desc')->paginate($request->take != null ? $request->take : 100000000);
    }

    public function register(Request $request)
    {
        $request->validate(
            [
                'name'                   => ['required', 'min:3', 'unique:companies'],
                'registration_number'    => ['required', 'min:5', 'unique:companies'],
                'short_form'             => ['required', 'min:3', 'max:6', 'unique:companies'],
                'license_number'         => ['required', 'min:5', 'unique:companies'],
                'tin'                    => ['required', 'min:6', 'unique:companies'],
                'category'               => ['required',],
                'email_address'          => ['required', 'email'],
                'phone_number'           => ['required', 'min:9', 'max:9', 'unique:companies'],
                'contact_person'         => ['required', 'min:3'],
                // 'sale_point_code'        => ['required', 'min:9', 'max:9', 'unique:companies'],
                
            ]);

        $company = Company::create(
            [
                'name'                  => $request->name,
                'registration_number'   => $request->registration_number,
                'short_form'            => $request->short_form,
                'license_number'        => $request->license_number,
                'tin'                   => $request->tin,
                'code'                  => 'ABL-'.date('His'),
                'sale_point_code'       => $request->sale_point_code,
                'category'              => $request->category,
                'logo'                  => $request->logo,
                'color'                 => $request->color,
                'email_address'         => $request->email_address,
                'postal_address'        => $request->postal_address,
                'phone_number'          => $request->phone_number,
                'contact_person'        => $request->contact_person,
                'created_by'            => intval(Auth::user()->id),
            ]);
        
      
            
            return redirect()->route('companies-registration')->with('success', 'Company <b>'.strtoupper($company->name).'</b> Successfully registered');
       
    }

    public function update(Request $request)
    {
        $company = Company::find($request->company_id);
        if(!$company)
        {
            return response()->json(['responseCode'=> 'NOT_FOUND', 'message' => "No Company with ID : ".$request->company_id]);
        }

        $request->validate(
            [
                'name'                   => ['min:3', 'unique:companies'],
                'registration_number'    => ['min:5', 'unique:companies'],
                'short_form'             => ['min:3', 'min:5', 'unique:companies'],
                'license_number'         => ['min:5', 'unique:companies'],
                'tin'                    => ['min:6', 'unique:companies'],
                'code'                   => ['min:3', 'unique:companies'],
                'sale_point_code'        => ['min:3', 'unique:companies'],
                'category'               => ['min:3'],
                'email_address'          => ['email'],
                'phone_number'           => ['min:9', 'max:9', 'unique:companies'],
                'contact_person'         => ['min:3'],
                'status'                 => ['in:Active,Inactive,Pending,Rejected']
            ]);

        $company->name = ($request->name != null) ? $request->name : $company->name;
        $company->registration_number = ($request->registration_number != null) ? $request->registration_number : $company->registration_number;
        $company->short_form = ($request->short_form != null) ? $request->short_form : $company->short_form;
        $company->license_number = ($request->license_number != null) ? $request->license_number :  $company->license_number;
        $company->tin = ($request->tin != null) ? $request->tin : $company->tin;
        $company->code = ($request->code != null) ? $request->code : $company->code;
        $company->sale_point_code = ($request->sale_point_code !=null) ?  $request->sale_point_code :  $company->sale_point_code;
        $company->category = ($request->category != null) ? $request->category : $company->category;
        $company->logo = ($request->logo != null) ? $request->logo : $company->logo;
        $company->email_address = ($request->email_address != null) ? $request->email_address : $company->email_address;
        $company->postal_address = ($request->postal_address != null) ? $request->postal_address : $company->postal_address;
        $company->phone_number = ($request->phone_number != null) ? $request->phone_number : $company->phone_number;
        $company->contact_person = ($request->contact_person != null) ? $request->contact_person : $company->contact_person;
        $company->status = ($request->status != null) ? $request->status : $company->status;
        $company->created_by = intval(Auth::user()->id);

        $company->save();

        return response()->json(['responseCode'=> 'SUCCESS','message'=>'Company Updated Successfully', 'company' => $company]);
    }
}