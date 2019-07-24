<?php

namespace App\Http\Controllers;

use App\AppointmentBooking;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Appointment;
use Auth;
use App\Availability;
use App\AppointmentField;
use App\AppointmentReminder;
use App\User;
use App\Employee;
use App\Product;
use App\ProductPackage;
use App\PipelineLead;
use App\ProductLead;
use App\PackageLead;
use App\Pipeline;
use App\Group;
use App\Tag;
use App\NotificationSetting;

use Tracker;
use Event;
use Log;
use Helper;

use App\Events\NewAppointmentBooking;

use EmployeeUserCommonFunctions;

use App\Jobs\CreateWebScreenshotImage;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','forceSSL','impersonate', 'enableduser', 'clientImpersonate', 'trialexpired', 'freeuserredirect', 'leadlevelredirect']);
    }

    public function index()
    {
        $user = Auth::user();

        $data = EmployeeUserCommonFunctions::myAppointmentsPageDataFetcher($user);

        $data['branding_details'] = $this->getMeBrandingDetails();

        if($user->has_seen_appointment_splash_flag == 0 && $user->agency_owner_id == NULL)
        {
            $user->has_seen_appointment_splash_flag = 1;
            $user->save();
            return view('memberpages.appointmentpages.myappointments_splash', $data);
        }
        else
        {
            return view('memberpages.appointmentpages.myappointments', $data);
        }


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
        else if($user->enterprise_owner_id)
        {
            $user_owner = User::where('id', $user->enterprise_owner_id)->first();
            if($user_owner)
            {
                $branding_details['agency_permalink'] = $user_owner->enterprise_permalink;
                $branding_details['agency_logo'] = $user_owner->enterprise_logo;
                $branding_details['agency_company_name'] = $user_owner->enterprise_company_name;
                $branding_details['agency_color'] = $user_owner->enterprise_color;
                $branding_details['agency_help_link'] = $user_owner->enterprise_help_link;
            }
        }


        return $branding_details;
    }

    public function createAppointment($appointment_id = '')
    {
        $data['appointment_id'] = '';
        $user = Auth::user();

        if ($appointment_id !== '')
        {
            $appointment = Appointment::where(['id' => trim($appointment_id), 'user_id' => $user->id])->first();
            #dd($appointment->id);
            if ($appointment)
            {
                $data['appointment_id'] = $appointment->id;
            }
        }
        $data['appointments'] = $user->appointments()->get();

        if($user->infusionsoftaccesstoken)
        {
            $data['infusionsoft_activated'] = 1;
            $data['infusionsoft_tag_categories'] = $this->fetchMeInfusionsoftTagCategories();
        }
        else
        {
            $data['infusionsoft_activated'] = 0;
        }
        $data['user'] = $user;
        $data['branding_details'] = $this->getMeBrandingDetails();

        if($user->can('create-employees'))
        {
            $employeesForThisUser = $user->employees()->get();
            $data['employees'] = $employeesForThisUser;
        }
        else
        {
            $data['employees'] = [];
        }

        $data['is_segmate_added'] = 0;

        if($user->segmate_details)
        {
            $segmate_details = json_decode($user->segmate_details, true);
            if(is_array($segmate_details))
            {
                $api_url = 'https://api.segmate.io/fanpages';

                $segmate_access_token = $segmate_details['access_token'];

                $headers = [
                            'Authorization' => 'Bearer ' . $segmate_access_token,
                            'Accept'        => 'application/json',
                        ];

                $segmateClient = new \GuzzleHttp\Client();

                try
                {
                    $res = $segmateClient->request('GET', $api_url, [
                        'headers' => $headers
                    ]);

                    if($res->getStatusCode() == "200")
                    {
                        $responseBody = json_decode($res->getBody(), true);
                        if($responseBody['success'])
                        {
                            $data['is_segmate_added'] = 1;
                            $data['segmate_fanpages'] = [];

                            if(isset($responseBody['fanpages']) && count($responseBody['fanpages']) > 0)
                            {
                                $data['segmate_fanpages'] = $responseBody['fanpages'];
                            }

                        }
                        else
                        {
                            $new_access_token = Helper::refreshSegmateToken($user);
                            if($new_access_token)
                            {
                                $api_url = 'https://api.segmate.io/fanpages';

                                $headers = [
                                            'Authorization' => 'Bearer ' . $new_access_token,
                                            'Accept'        => 'application/json',
                                        ];

                                $segmateClient = new \GuzzleHttp\Client();

                                $res = $segmateClient->request('GET', $api_url, [
                                    'headers' => $headers
                                ]);

                                $responseBody = json_decode($res->getBody(), true);
                                if($responseBody['success'])
                                {
                                    $data['is_segmate_added'] = 1;
                                    $data['segmate_fanpages'] = [];

                                    if(isset($responseBody['fanpages']) && count($responseBody['fanpages']) > 0)
                                    {
                                        $data['segmate_fanpages'] = $responseBody['fanpages'];
                                    }
                                }
                            }
                        }
                    }
                }
                catch(\GuzzleHttp\Exception\ClientException $e)
                {
                    $new_access_token = Helper::refreshSegmateToken($user);
                    if($new_access_token)
                    {
                        $api_url = 'https://api.segmate.io/fanpages';

                        $headers = [
                                    'Authorization' => 'Bearer ' . $new_access_token,
                                    'Accept'        => 'application/json',
                                ];

                        $segmateClient = new \GuzzleHttp\Client();

                        $res = $segmateClient->request('GET', $api_url, [
                            'headers' => $headers
                        ]);

                        $responseBody = json_decode($res->getBody(), true);
                        if($responseBody['success'])
                        {
                            $data['is_segmate_added'] = 1;
                            $data['segmate_fanpages'] = [];

                            if(isset($responseBody['fanpages']) && count($responseBody['fanpages']) > 0)
                            {
                                $data['segmate_fanpages'] = $responseBody['fanpages'];
                            }
                        }
                    }
                }
                catch(\Exception $e)
                {
                    $new_access_token = Helper::refreshSegmateToken($user);
                    if($new_access_token)
                    {
                        $api_url = 'https://api.segmate.io/fanpages';

                        $headers = [
                                    'Authorization' => 'Bearer ' . $new_access_token,
                                    'Accept'        => 'application/json',
                                ];

                        $segmateClient = new \GuzzleHttp\Client();

                        $res = $segmateClient->request('GET', $api_url, [
                            'headers' => $headers
                        ]);

                        $responseBody = json_decode($res->getBody(), true);
                        if($responseBody['success'])
                        {
                            $data['is_segmate_added'] = 1;
                            $data['segmate_fanpages'] = [];

                            if(isset($responseBody['fanpages']) && count($responseBody['fanpages']) > 0)
                            {
                                $data['segmate_fanpages'] = $responseBody['fanpages'];
                            }
                        }
                    }
                }
            }
        }

        $data['basic_domain'] = $user->whitelebals()->where('domain_type', 'basic')->first();


        $allUserGroups = Group::where('user_id', $user->id)->get();

        $groupsWithTags = [];

        $tagsForTheDefaultGroup = Tag::where([
												['group_id', 0],
												['user_id', $user->id]
											])->get();

        if(count($tagsForTheDefaultGroup) > 0)
        {
            $singleGroupWithTag = [];
            $singleGroupWithTag['name'] = 'Default';
            $singleGroupWithTag['tags'] = $tagsForTheDefaultGroup;
            $groupsWithTags[] = $singleGroupWithTag;
        }

        foreach ($allUserGroups as $singleUserGroup)
        {
            $tagsForTheGroup = Tag::where('group_id', $singleUserGroup->id)->get();
            if(count($tagsForTheGroup) > 0)
            {
                $singleGroupWithTag = [];
                $singleGroupWithTag['name'] = $singleUserGroup->name;
                $singleGroupWithTag['tags'] = $tagsForTheGroup;
                $groupsWithTags[] = $singleGroupWithTag;
            }
        }


        $data['groupsWithTags'] = $groupsWithTags;


        return view('memberpages.appointmentpages.createappointment', $data);
    }

    public function fetchMeInfusionsoftTagCategories()
    {
        $user = Auth::user();
        $infusionAccessToken = $user->infusionsoftaccesstoken;

        $infusionsoft = new \Infusionsoft\Infusionsoft(array(
            'clientId' => config('constants.IF_CLIENT_ID'),
            'clientSecret' => config('constants.IF_CLIENT_SECRET'),
            'redirectUri' => url('/') . '/members/infusionsoft-callback',
        ));
        $infusionsoft->setToken(unserialize(base64_decode($user->infusionsoftaccesstoken)));

        if($infusionsoft->isTokenExpired())
        {
            $infusionsoft->refreshAccessToken();
            $user->infusionsoftaccesstoken = base64_encode(serialize($infusionsoft->getToken()));
            $user->save();
        }

        $categories = [];
        $page = 0;

        do
        {
            $result = $infusionsoft->data->query('ContactGroupCategory', 1000, $page, ['id' => '%'], ['id', 'CategoryName'], 'CategoryName', true);
            $categories = array_merge($categories, $result);

        } while (count($result) === 1000);

        return $categories;
    }

    public function saveAppointment(Request $request)
    {
        if ($request->input('name'))
        {
            $unique_code = '';
            if ($request->input('appointment_id'))
            {
                $appointment = Appointment::where('id', trim($request->input('appointment_id')))->first();
                if (!$appointment)
                {
                    $appointment = new Appointment();
                }
            }
            else
            {
                $appointment = new Appointment();
            }

            $user = Auth::user();
            $appointment->user_id = $user->id;
            $appointment->name = trim($request->input('name'));
            $appointment->timezone = trim($request->input('timezone'));
            $appointment->description = trim($request->input('description'));
            $appointment->instructions_type = trim($request->input('instructions_type'));
            $appointment->is_charged = trim($request->input('is_charged'));
            $appointment->payment_gateway = trim($request->input('payment_gateway'));
            $appointment->amount = trim($request->input('amount'));
            $appointment->currency = trim($request->input('currency'));

            $appointment->is_assigned = trim($request->input('is_assigned'));
            $appointment->assigned_to = json_encode(($request->input('selectedEmployeesList')));

            if($request->input('is_assigned') && is_array($request->input('selectedEmployeesList')))
            {
                $employeesList = $request->input('selectedEmployeesList');
                $appointment->round_robin_tracker = $employeesList[0];
            }
            else
            {
                $appointment->round_robin_tracker = NULL;
            }

            if(trim($request->input('custom_instructions')))
            {
                $appointment->custom_instructions = trim($request->input('custom_instructions'));
            }
            else
            {
                $appointment->custom_instructions = NULL;
            }


            //optional parameters handling starts

            //slot length handling starts
            if ($request->input('slot_length_day') || $request->input('slot_length_hour') || $request->input('slot_length_minute'))
            {
                $appointment->duration = (trim($request->input('slot_length_day')) * 86400) + (trim($request->input('slot_length_hour')) * 3600) + (trim($request->input('slot_length_minute')) * 60);//duration in seconds
                if ($appointment->duration>86400)
                {
                    $appointment->duration=86400;
                }
            }
            //slot length handling ends

            //buffer before slots handling starts
            if ($request->has('buffer_before_day') || $request->has('buffer_before_hour') || $request->has('buffer_before_minute'))
            {
                if($request->input('buffer_before_day') == 0 && $request->input('buffer_before_hour') == 0 && $request->input('buffer_before_minute') == 0)
                {
                    $appointment->buffer_time_before = NULL;
                }
                else
                {
                    $appointment->buffer_time_before = (trim($request->input('buffer_before_day')) * 86400) + (trim($request->input('buffer_before_hour')) * 3600) + (trim($request->input('buffer_before_minute')) * 60);//duration in seconds
                }
            }
            //buffer before slots handling ends

            //buffer after slots handling starts
            if ($request->has('buffer_after_day') || $request->has('buffer_after_hour') || $request->has('buffer_after_minute'))
            {
                if($request->input('buffer_after_day') == 0 && $request->input('buffer_after_hour') == 0 && $request->input('buffer_after_minute') == 0)
                {
                    $appointment->buffer_time_after = NULL;
                }
                else
                {
                    $appointment->buffer_time_after = (trim($request->input('buffer_after_day')) * 86400) + (trim($request->input('buffer_after_hour')) * 3600) + (trim($request->input('buffer_after_minute')) * 60);//duration in seconds
                }
            }
            //buffer after slots handling ends

            //adavance booking time min handling starts
            if ($request->has('advance_book_time_min') && $request->has('advance_book_time_min_type'))
            {
                $advance_min = 0;
                if (trim($request->input('advance_book_time_min_type')) == 'hour')
                {
                    $advance_min = trim($request->input('advance_book_time_min')) * 3600;
                }
                elseif (trim($request->input('advance_book_time_min_type')) == 'day')
                {
                    $advance_min = trim($request->input('advance_book_time_min')) * 86400;
                }
                $appointment->advance_book_time_min = $advance_min;//duration in seconds
                $appointment->advance_book_time_min_type = trim($request->input('advance_book_time_min_type'));
            }

            if ($request->has('advance_book_time_min_check') && $request->input('advance_book_time_min_check')==0)
            {
                $appointment->advance_book_time_min = null;
                $appointment->advance_book_time_min_type = null;
            }
            //advance booking time min handling ends

            //advance booking time max handling starts
            if ($request->has('advance_book_time_max') && $request->has('advance_book_time_max_type'))
            {
                $advance_max = 0;
                if (trim($request->input('advance_book_time_max_type')) == 'hour')
                {
                    $advance_max = trim($request->input('advance_book_time_max')) * 3600;
                }
                elseif (trim($request->input('advance_book_time_max_type')) == 'day')
                {
                    $advance_max = trim($request->input('advance_book_time_max')) * 86400;
                }
                $appointment->advance_book_time_max = $advance_max;//duration in seconds
                $appointment->advance_book_time_max_type = trim($request->input('advance_book_time_max_type'));
            }

            if ($request->has('advance_book_time_max_check') && $request->input('advance_book_time_max_check')==0)
            {
                $appointment->advance_book_time_max = null;
                $appointment->advance_book_time_max_type = null;
            }
            //advance booking time max handling ends

            //max slots to list per day handling starts
            if ($request->has('max_slots_to_list_per_day'))
            {
                $max_slots_to_list_per_day = 0;
                if (trim($request->input('max_slots_to_list_per_day')) > 0)
                {
                    $max_slots_to_list_per_day = trim($request->input('max_slots_to_list_per_day'));
                }
                $appointment->max_slots_to_display = $max_slots_to_list_per_day;



                //randomize availability
                $appointment_full_object= $user->appointments()->where('id',$appointment->id)->first();
                if(!empty($appointment->max_slots_to_display) && !empty($appointment_full_object['duration']))
                {
                    $slots_order_to_display = Helper::getWeekOfRandomSlots($appointment->max_slots_to_display);
                    $appointment->slots_order_to_display = serialize($slots_order_to_display);

                }

            }

            if($request->has('max_slots_to_list_per_day_check') && $request->input('max_slots_to_list_per_day_check')==0)
            {
                $appointment->max_slots_to_display=0;
            }
            //max slots to list per day handling ends

            //allow reschedule handling starts
            if ($request->has('allow_reschedule'))
            {
                $appointment->allow_reschedule_or_cancel = $request->input('allow_reschedule');
            }
            //allow reschedule handling ends

            //attendees per slot handling starts
            if ($request->input('attendees_per_slot'))
            {
                if (trim($request->input('attendees_per_slot')) > 1)
                {
                    $appointment->attendees_per_slot = trim($request->input('attendees_per_slot'));
                }
                else
                {
                    $appointment->attendees_per_slot = 1;
                }
            }
            //attendees per slot handling ends


            // appointment date time range handling starts
            if ($request->input('appointment_datetimerange'))
            {
                $date_time_range=trim($request->input('appointment_datetimerange'));
                $date_time_parts=array_filter(explode(' - ', $date_time_range));
                if (count($date_time_parts)!=2)
                {
                    return response()->json(['status' => 'failed', 'message' => 'Invalid appointment date time range.']);
                }
                else
                {
                    $date_time_1_parts=array_filter(explode(' ', $date_time_parts[0]));
                    $date_time_2_parts=array_filter(explode(' ', $date_time_parts[1]));

                    if (count($date_time_1_parts)!=3 || count($date_time_2_parts)!=3)
                    {
                        return response()->json(['status' => 'failed', 'message' => 'Invalid appointment date time range.']);
                    }
                    else
                    {
                        if ((strtolower(trim($date_time_1_parts[2]))!='am' && strtolower(trim($date_time_1_parts[2]))!='pm') || (strtolower(trim($date_time_2_parts[2]))!='am' && strtolower(trim($date_time_2_parts[2]))!='pm'))
                        {
                            return response()->json(['status' => 'failed', 'message' => 'Invalid appointment date time range.']);
                        }
                        else
                        {
                            $date_1_parts=array_filter(explode('/', $date_time_1_parts[0]));
                            $date_2_parts=array_filter(explode('/', $date_time_2_parts[0]));
                            if (count($date_1_parts)!=3 || count($date_2_parts)!=3)
                            {
                                return response()->json(['status' => 'failed', 'message' => 'Invalid appointment date time range.']);
                            }
                            else
                            {
                                $time_1_parts=array_filter(explode(':', $date_time_1_parts[1]));
                                $time_2_parts=array_filter(explode(':', $date_time_2_parts[1]));
                                if (count($time_1_parts)!=2 || count($time_2_parts)!=2)
                                {
                                    return response()->json(['status' => 'failed', 'message' => 'Invalid appointment date time range.']);
                                }
                            }
                        }
                    }
                }
                $appointment->date_time_range = trim($request->input('appointment_datetimerange'));
            }
            // appointment date time range handling ends



            if ($request->has('segmate_details'))
            {
                if ($request->input('segmate_details')['is_segmate_enabled'] == 1)
                {
                    $appointment->segmate_details = json_encode($request->input('segmate_details'));
                }
                else
                {
                    $appointment->segmate_details = NULL;
                }
            }


            //customization section starts
            //show logo section starts
            if ($request->has('show_logo'))
            {
                if ($request->input('show_logo')==1 && $request->input('logo_image')!=null)
                {
                    $appointment->show_logo=1;
                    $appointment->logo_image_id=trim($request->input('logo_image'));
                }
                else
                {
                    $appointment->show_logo=0;
                    $appointment->logo_image_id=null;
                }
            }
            //show logo section ends

            //show profile image section starts
            if ($request->has('show_profile_image'))
            {
                if ($request->input('show_profile_image')==1)
                {
                    $appointment->show_profile_image=1;
                }
                else
                {
                    $appointment->show_profile_image=0;
                }
            }
            //show profile image section ends

            //show video section starts
            if ($request->has('show_video'))
            {
                if ($request->input('show_video')==1 && $request->input('video')!=null)
                {
                    $appointment->show_video=1;
                    $appointment->video_provider=trim($request->input('video')['provider']);
                    $appointment->video_id=trim($request->input('video')['id']);
                }
                else
                {
                    $appointment->show_video=0;
                    $appointment->video_provider=null;
                    $appointment->video_id=null;
                }
            }
            //show video section ends

            //show related appointments section starts
            if ($request->has('related_appointments_check'))
            {
                if ($request->input('related_appointments_check') == 1 && count($request->input('related_appointments_list')) > 0)
                {
                    $appointment->related_appointments = json_encode($request->input('related_appointments_list'));
                }
                else
                {
                    $appointment->related_appointments = null;
                }
            }
            //show related appointments section ends


            //show related appointments section starts
            if ($request->has('social_media_icons_check'))
            {
                if ($request->input('social_media_icons_check') == 1)
                {
                    $appointment->show_social_media_icons = 1;

                    if ($request->has('social_media_fb_check') && $request->input('social_media_fb_check') == 1)
                    {
                        $appointment->show_facebook_icon = 1;
                    }
                    else
                    {
                        $appointment->show_facebook_icon = 0;
                    }


                    if ($request->has('social_media_twitter_check') && $request->input('social_media_twitter_check') == 1)
                    {
                        $appointment->show_twitter_icon = 1;
                    }
                    else
                    {
                        $appointment->show_twitter_icon = 0;
                    }


                    if ($request->has('social_media_linkedin_check') && $request->input('social_media_linkedin_check') == 1)
                    {
                        $appointment->show_linkedin_icon = 1;
                    }
                    else
                    {
                        $appointment->show_linkedin_icon = 0;
                    }
                }
                else
                {
                    $appointment->show_social_media_icons = 0;
                    $appointment->show_facebook_icon = 0;
                    $appointment->show_twitter_icon = 0;
                    $appointment->show_linkedin_icon = 0;
                }
            }
            //show related appointments section ends


            //show redirect custom url section starts
            if ($request->has('redirect_type'))
            {
                if ($request->input('redirect_type') == 1)
                {
                    $appointment->redirect_type = 1;
                    $appointment->redirect_url = NULL;
                }
                else if ($request->input('redirect_type') == 2)
                {
                    $appointment->redirect_type = 2;
                    $appointment->redirect_url = $request->input('redirect_url');
                }
            }
            //show redirect custom url section ends

            //Header Background & Fonts Color
            $colorArray = array();
            //dd($request->input('headerBackgroundColor'));
            if ($request->has('headerBackgroundColor'))
            {
                $colorArray['headerBackgroundColor'] = $request->input('headerBackgroundColor');
            }
            if ($request->has('headerFontColor'))
            {
                $colorArray['headerFontColor'] = $request->input('headerFontColor');
            }
            if ($request->has('headerBackgroundImage'))
            {
                $colorArray['headerBackgroundImage'] = $request->input('headerBackgroundImage');
            }
            if ($request->has('calenderBackgroundColor'))
            {
                $colorArray['calenderBackgroundColor'] = $request->input('calenderBackgroundColor');
            }
            if ($request->has('calenderFontColor'))
            {
                $colorArray['calenderFontColor'] = $request->input('calenderFontColor');
            }
            if ($request->has('calenderHeaderColor'))
            {
                $colorArray['calenderHeaderColor'] = $request->input('calenderHeaderColor');
            }
            if ($request->has('calenderHeaderFontColor'))
            {
                $colorArray['calenderHeaderFontColor'] = $request->input('calenderHeaderFontColor');
            }
            if ($request->has('slotsBackgroundColor'))
            {
                $colorArray['slotsBackgroundColor'] = $request->input('slotsBackgroundColor');
            }
            if ($request->has('slotsFontColor'))
            {
                $colorArray['slotsFontColor'] = $request->input('slotsFontColor');
            }
            if ($request->has('noSlotsAlertBackground'))
            {
                $colorArray['noSlotsAlertBackground'] = $request->input('noSlotsAlertBackground');
            }
            if ($request->has('noSlotsAlertFont'))
            {
                $colorArray['noSlotsAlertFont'] = $request->input('noSlotsAlertFont');
            }

            if ($request->has('bodyFontColor'))
            {
                $colorArray['bodyFontColor'] = $request->input('bodyFontColor');
            }


            if ($request->has('appointmentFormBackground'))
            {
                $colorArray['appointmentFormBackground'] = $request->input('appointmentFormBackground');
            }
            if ($request->has('appointmentFormFont'))
            {
                $colorArray['appointmentFormFont'] = $request->input('appointmentFormFont');
            }

            if ($request->has('appointmentFormButtonBackground'))
            {
                $colorArray['appointmentFormButtonBackground'] = $request->input('appointmentFormButtonBackground');
            }
            if ($request->has('appointmentFormButtonFont'))
            {
                $colorArray['appointmentFormButtonFont'] = $request->input('appointmentFormButtonFont');
            }

            if ($request->has('bookAppointmentButtonBackground'))
            {
                $colorArray['bookAppointmentButtonBackground'] = $request->input('bookAppointmentButtonBackground');
            }

            if ($request->has('bookAppointmentButtonFont'))
            {
                $colorArray['bookAppointmentButtonFont'] = $request->input('bookAppointmentButtonFont');
            }
            if ($request->has('bookThisButtonBackground'))
            {
                $colorArray['bookThisButtonBackground'] = $request->input('bookThisButtonBackground');
            }
            if ($request->has('bookThisButtonFont'))
            {
                $colorArray['bookThisButtonFont'] = $request->input('bookThisButtonFont');
            }


            if(sizeof($colorArray) > 0)
            {
                $appointment->custom_css = base64_encode(serialize($colorArray));
            }

            if ($request->has('is_show_slots_check'))
            {
                if ($request->input('is_show_slots_check') == 1)
                {
                    $appointment->is_show_slots = 1;
                }
                else
                {
                    $appointment->is_show_slots = 0;
                }
            }

            //Header Background & Fonts Color

            //customization section ends



            // Availability Type
            if ($request->input('availability_type'))
            {
                $appointment->availability_type = $request->input('availability_type');
            }

            // appointment availabilities handling starts
            if ($request->input('availabilities'))
            {
                $availabilities = $request->input('availabilities');
                $avail = Availability::where('appointment_id', $appointment->id)->delete();
                foreach ($availabilities as $key => $value)
                {
                    $avail = new Availability();
                    if (isset($value['day']) && isset($value['start']) && isset($value['end']))
                    {

                        $randomizeSlots = "";

                        $avail->appointment_id = $appointment->id;
                        $start = $this->makeTimeZoneAdjustmentWhenSaving($value['start'], $user);
                        $end = $this->makeTimeZoneAdjustmentWhenSaving($value['end'], $user);
                        $avail->day = $value['day'];
                        $avail->start = $start;
                        $avail->end = $end;
                        $avail->save();
                    }
                }
            }
            // appointment availabilities handling ends

            // appointment required fields handling starts
            if ($request->input('required_fields'))
            {
                $fields=$request->input('required_fields');
                AppointmentField::where('appointment_id', $appointment->id)->delete();
                foreach ($fields as $key => $value)
                {
                    $required_field=new AppointmentField();
                    $required_field->appointment_id=$appointment->id;
                    $required_field->field=$value['field'];
                    $required_field->type=$value['type'];
                    $required_field->required=$value['required'];
                    $required_field->save();
                }
            }
            // appointment required fields handling ends

            if ($request->has('required_mandatory'))
            {
                $appointment->is_mandatory = $request->input('required_mandatory');
            }

            if ($request->has('reminder_type'))
            {
                $appointment->reminder_type = $request->input('reminder_type');
                AppointmentReminder::where('appointment_id', $appointment->id)->delete();
            }

            // appointment reminders handling starts
            if ($request->has('reminders'))
            {
                $reminders = $request->input('reminders');
                foreach ($reminders as $key => $value)
                {
                    if(!empty($value))
                    {
                        $reminder = new AppointmentReminder();
                        $reminder->appointment_id = $appointment->id;
                        $reminder->type = 'mail';
                        $reminder->sendto = $value['reminderReceiver'];
                        $reminder->secondsbefore = $value['noOfSeconds'];
                        $reminder->save();
                    }
                }
            }
            // appointment reminders handling ends


            //automated actions handling starts
            if ($request->has('make_appointment_action'))
            {
                $appointment->make_appointment_action = base64_encode(serialize($request->input('make_appointment_action')));
            }

            if ($request->has('cancel_appointment_action'))
            {
                $appointment->cancel_appointment_action = base64_encode(serialize($request->input('cancel_appointment_action')));
            }

            if ($request->has('show_up_appointment_action'))
            {
                $appointment->show_up_appointment_action = base64_encode(serialize($request->input('show_up_appointment_action')));
            }

            if ($request->has('sold_appointment_action'))
            {
                $appointment->sold_appointment_action = base64_encode(serialize($request->input('sold_appointment_action')));
            }

            if ($request->has('made_appointment_internal_tag_switch_checker'))
            {
                if($request->input('made_appointment_internal_tag_switch_checker') == 1 && $request->input('made_appointment_internal_tags'))
                {
                    $appointment->book_appointment_internal_tag = json_encode($request->input('made_appointment_internal_tags'));
                }
                else
                {
                    $appointment->book_appointment_internal_tag = NULL;
                }
            }

            if ($request->has('cancel_appointment_internal_tag_switch_checker'))
            {
                if($request->input('cancel_appointment_internal_tag_switch_checker') == 1 && $request->input('cancel_appointment_internal_tags'))
                {
                    $appointment->cancel_appointment_internal_tag = json_encode($request->input('cancel_appointment_internal_tags'));
                }
                else
                {
                    $appointment->cancel_appointment_internal_tag = NULL;
                }
            }

            if ($request->has('sold_appointment_internal_tag_switch_checker'))
            {
                if($request->input('sold_appointment_internal_tag_switch_checker') == 1 && $request->input('sold_appointment_internal_tags'))
                {
                    $appointment->sold_appointment_internal_tag = json_encode($request->input('sold_appointment_internal_tags'));
                }
                else
                {
                    $appointment->sold_appointment_internal_tag = NULL;
                }
            }


            if($request->input('pipeline'))
            {
                $appointment->pipeline_id = $request->input('pipeline_value');
            }

            if($request->input('product_or_package'))
            {
                switch($request->input('product_or_package'))
                {
                    case 'package':
                        $appointment->package_id = $request->input('product_or_package_value');
                        $appointment->product_id = 0;
                        break;
                    case 'product':
                        $appointment->product_id = $request->input('product_or_package_value');
                        $appointment->package_id = 0;
                        break;
                    default:
                        $appointment->product_id = 0;
                        $appointment->package_id = 0;
                        break;
                }
            }


            //optional parameters handling ends
            $appointment->save();

            if ($appointment->unique_code == null)
            {
                $id_got = false;
                $no_of_dig = 10;
                while ($id_got == false)
                {
                    $rand_id = rand(pow(10, $no_of_dig - 1), pow(10, $no_of_dig) - 1);
                    $unique_code = $appointment->id . $rand_id;
                    $is_exist_id = Appointment::where('unique_code', $unique_code)->first();
                    if (!$is_exist_id)
                    {
                        $id_got = true;
                        $appointment->unique_code = $unique_code;
                        $appointment->save();
                    }
                }
            }

            Helper::markWizardStepAsCompleted($user, 'create_appointment');


            $permalink = $appointment->unique_code;
            $url  = url('/') . '/appointment-new/book/' . $permalink;
            $createscreenshotImageJob = new CreateWebScreenshotImage('appointment', $permalink, $url, 1);
            dispatch($createscreenshotImageJob);

            return response()->json(['status' => 'success', 'appointment_id' => $appointment->id, 'unique_code' => $appointment->unique_code]);
        }
        else
        {
            return response()->json(['status' => 'failed', 'message' => 'Please enter appointment name.']);
        }
    }

    public function makeTimeZoneAdjustmentWhenSaving($timestamp, $user)
    {
        $usersTimeZone = $user->timezone;

        $user_dtz = new \DateTimeZone($usersTimeZone);
        $user_dt = new \DateTime("now", $user_dtz);

        $offset = $user_dtz->getOffset($user_dt);

        //This is needed because we have to subtract when there's addition
        //and add when theres subtraction
        $offset = $offset * -1;

        $adjustedTimeStamp = $timestamp + $offset;

        return $adjustedTimeStamp;
    }

    public function makeTimeZoneAdjustmentWhenFetching($timestamp, $user)
    {
        $usersTimeZone = $user->timezone;

        $user_dtz = new \DateTimeZone($usersTimeZone);
        $user_dt = new \DateTime("now", $user_dtz);

        $offset = $user_dtz->getOffset($user_dt);

        $adjustedTimeStamp = $timestamp + $offset;

        return $adjustedTimeStamp;
    }

    public function convertAvaiabilitiesToThisWeek($timestamp, $timestampday)
    {
        $dt = strtotime('today');

        $todaysNumber = date('N', $dt);

        if($timestampday == 0)
        {
            $thisWeekSunday = $todaysNumber == 7 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('last sunday', $dt));
            $finalDate = $thisWeekSunday;
        }
        else if($timestampday == 6)
        {
            $thisWeekSaturday = $todaysNumber == 6 ? date('Y-m-d', $dt) : date('Y-m-d', strtotime('next saturday', $dt));
            $finalDate = $thisWeekSaturday;
        }
        else
        {
            switch($timestampday)
            {
                case 1:
                    $dayInName = 'monday';
                    break;
                case 2:
                    $dayInName = 'tuesday';
                    break;
                case 3:
                    $dayInName = 'wednesday';
                    break;
                case 4:
                    $dayInName = 'thursday';
                    break;
                case 5:
                    $dayInName = 'friday';
                    break;
                default:
                    break;
            }

            if($todaysNumber == $timestampday)
            {
                $thisWeekTranslatedDay = date('Y-m-d', $dt);
            }
            else if($todaysNumber == 7)
            {
                $thisWeekTranslatedDay = date('Y-m-d', strtotime('next ' . $dayInName, $dt));
            }
            else if($todaysNumber == 6)
            {
                $thisWeekTranslatedDay = date('Y-m-d', strtotime('last ' . $dayInName, $dt));
            }
            else if($todaysNumber < $timestampday)
            {
                $thisWeekTranslatedDay = date('Y-m-d', strtotime('next ' . $dayInName, $dt));
            }
            else
            {
                $thisWeekTranslatedDay = date('Y-m-d', strtotime('last ' . $dayInName, $dt));
            }

            $finalDate = $thisWeekTranslatedDay;
        }

        $timeInUTC = date('h:i:s a',$timestamp);

        $completeTimeString = $finalDate . ' ' . $timeInUTC;

        $finalTranslatedTimeStamp = strtotime($completeTimeString);

        return $finalTranslatedTimeStamp;
    }

    public function getAppointmentDetails(Request $request)
    {
        if ($request->input('appointment_id'))
        {
            $user = Auth::user();
            $appointment = $user->appointments()->where('id', trim($request->input('appointment_id')))->first();
            $appointmentAvailabilities = Availability::where('appointment_id', trim($request->input('appointment_id')))->get();

            foreach($appointmentAvailabilities as $singleAppointmentAvailability)
            {
                $singleAppointmentAvailability->start = $this->makeTimeZoneAdjustmentWhenFetching($singleAppointmentAvailability->start, $user);
                $singleAppointmentAvailability->end = $this->makeTimeZoneAdjustmentWhenFetching($singleAppointmentAvailability->end, $user);

                $singleAppointmentAvailability->start = $this->convertAvaiabilitiesToThisWeek($singleAppointmentAvailability->start, $singleAppointmentAvailability->day);
                $singleAppointmentAvailability->end = $this->convertAvaiabilitiesToThisWeek($singleAppointmentAvailability->end, $singleAppointmentAvailability->day);
            }

            $appointment->availabilities = $appointmentAvailabilities;

            $appointment->required_fields = AppointmentField::where('appointment_id', trim($request->input('appointment_id')))->get();

            $appointment->reminders = AppointmentReminder::where('appointment_id', trim($request->input('appointment_id')))->get();

            $appointment->user_timezone=$user->timezone;

            $appointment->readable_make_appointment_action = unserialize(base64_decode($appointment->make_appointment_action));
            $appointment->readable_cancel_appointment_action = unserialize(base64_decode($appointment->cancel_appointment_action));
            $appointment->readable_show_up_appointment_action = unserialize(base64_decode($appointment->show_up_appointment_action));
            $appointment->readable_sold_appointment_action = unserialize(base64_decode($appointment->sold_appointment_action));

            $appointment->readable_book_appointment_internal_tag = json_decode($appointment->book_appointment_internal_tag, true);
            $appointment->readable_cancel_appointment_internal_tag = json_decode($appointment->cancel_appointment_internal_tag, true);
            $appointment->readable_sold_appointment_internal_tag = json_decode($appointment->sold_appointment_internal_tag, true);


            $appointment->custom_css = unserialize(base64_decode($appointment->custom_css));

            return response()->json(['status' => 'success', 'appointment' => $appointment]);
        }
        else
        {
            return response()->json(['status' => 'failed']);
        }
    }

    public function viewBookings(Request $request, $appointment_id = '')
    {
        $user = Auth::user();

        $data = EmployeeUserCommonFunctions::appointmentViewBookingsDataFetcher($user, $appointment_id);

        $data['branding_details'] = $this->getMeBrandingDetails();
        return view('memberpages.appointmentpages.viewbookings', $data);
    }

    public function addRetargetingCodes(Request $request)
    {
        $response = ['status' => 'failed', 'title' => 'Error', 'message' => 'Error message', 'type' => 'error'];

        $appointment_id = $request->input('appointment_id');

        if($appointment_id != '')
        {
            $user = Auth::user();

            $response = EmployeeUserCommonFunctions::appointmentAddRetargetingCodeAction($user, $appointment_id, $request);

            return response()->json($response);
        }
        else
        {
            abort(401);
        }
    }

    public function addAssignedEmployees(Request $request)
    {
        $response = ['status' => 'failed', 'title' => 'Error', 'message' => 'Error message', 'type' => 'error'];

        $appointment_id = $request->input('appt_id');

        if($appointment_id != '')
        {
            $user = Auth::user();

            $response = EmployeeUserCommonFunctions::appointmentAssignEmployeesAction($user, $appointment_id, $request);

            Helper::markWizardStepAsCompleted($user, 'assign_campaign');
            return response()->json($response);
        }
        else
        {
            abort(401);
        }
    }


    public function addAssignedTags(Request $request)
    {
        $response = ['status' => 'failed', 'title' => 'Error', 'message' => 'Error message', 'type' => 'error'];

        $appointment_id = $request->input('appt_id');

        if($appointment_id != '')
        {
            $user = Auth::user();

            $response = EmployeeUserCommonFunctions::appointmentAssignTagsAction($user, $appointment_id, $request);

            return response()->json($response);
        }
        else
        {
            abort(401);
        }
    }

    public function addFacebookSettings(Request $request)
    {
        $response = ['status' => 'failed', 'title' => 'Error', 'message' => 'Error message', 'type' => 'error'];

        $appointment_id = $request->input('appointment_id');

        if($appointment_id != '')
        {
            $user = Auth::user();
            $response = EmployeeUserCommonFunctions::appointmentAddFacebookSettingsAction($user, $appointment_id, $request);

            return response()->json($response);
        }
        else
        {
            abort(401);
        }
    }

    public function addNotificationSettings(Request $request)
    {
        $response = ['status' => 'failed', 'title' => 'Error', 'message' => 'Error message', 'type' => 'error'];

        $appt_id = $request->input('appt_id');

        if($appt_id != '')
        {
            $user = Auth::user();

            $response = EmployeeUserCommonFunctions::appointmentAddNotificationSettingsAction($user, $appt_id, $request);

            return response()->json($response);
        }
        else
        {
            abort(401);
        }
    }
}
