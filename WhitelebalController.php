<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Auth;
use App\Whitelebal;

class WhitelebalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','forceSSL', 'impersonate', 'enableduser', 'clientImpersonate', 'trialexpired', 'freeuserredirect', 'leadlevelredirect']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['branding_details'] = $this->getMeBrandingDetails();
        $data['domains'] = Whitelebal::where('user_id', Auth::id())->get();
        return view('whitelebal.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $data['branding_details'] = $this->getMeBrandingDetails();
      return view('whitelebal.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $this->validate($request, [
        'domain_name' => 'required|url|unique:whitelebals,domain_name,'.$request->id
      ]);

      $whitelebal = Whitelebal::firstOrNew(['id' => $request->id]);
      $whitelebal->user_id = Auth::id();
      $whitelebal->domain_name = $request->domain_name;
      $whitelebal->domain_type = $request->domain_type;
      $whitelebal->save();

      return redirect('/whitelebal');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $data['branding_details'] = $this->getMeBrandingDetails();
      $domain = Whitelebal::where('user_id', Auth::id())->where('id',$id)->first();

      if(!($domain))
        return back();

      $data['domain'] = $domain;
      return view('whitelebal.create', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $whitelebal = Whitelebal::find($id);

        if(!($whitelebal))
          return back();

        $whitelebal->delete();
        return back();
    }


    public function getMeBrandingDetails()
    {
        $user = Auth::user();

        $branding_details = [];

        if($user->agency_owner_id)
        {
            $user_owner = User::where('id', $user->agency_owner_id)->first();
            if($user_owner)
            {
                $branding_details['agency_permalink'] = $user_owner->agency_permalink;
                $branding_details['agency_logo'] = $user_owner->agency_logo;
                $branding_details['agency_company_name'] = $user_owner->agency_company_name;
                $branding_details['agency_color'] = $user_owner->agency_color;
                $branding_details['agency_help_link'] = $user_owner->agency_help_link;
            }
        }

        return $branding_details;
    }
}
