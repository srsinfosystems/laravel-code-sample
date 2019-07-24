@extends('layouts.publicpages')

@section('headertitle')
    {{$survey->name}} Confirmation
@endsection

@section('page-level-plugins-css')
@endsection

@section('page-head-retargeting-code')
    @if($survey->head_retargeting_code != '' && $survey->head_retargeting_code != NULL)
        {!! base64_decode($survey->head_retargeting_code) !!}
    @endif
@endsection


@section('page-body-top-retargeting-code')
    @if($survey->body_top_retargeting_code != '' && $survey->body_top_retargeting_code != NULL)
        {!! base64_decode($survey->body_top_retargeting_code) !!}
    @endif
@endsection


@section('pagecontent')

    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <form role="form">
                <div class="form-body">
                    <div class="form-container publicFacingQuestionsContainer center-block">
                        <div class="row">
                            <div class="col-md-1"></div>
                            <div class="col-md-10">
                                <div class="resetCSSToDefault">
                                    <div class="survay-optin-container">

                                        <?php
                                            if(array_key_exists('headline', $leadActionPage))
                                            {
                                        ?>
                                            <div class="survay-optin-headline-container text-center">
                                                {{ucfirst($leadActionPage['headline'])}}
                                            </div>
                                        <?php
                                            }
                                        ?>

                                        <?php
                                            if(array_key_exists('subheadline', $leadActionPage))
                                            {
                                        ?>
                                                <div class="survay-optin-subheadline-container text-center">
                                                    {{ucfirst($leadActionPage['subheadline'])}}
                                                </div>
                                        <?php
                                            }
                                        ?>

                                        <?php
                                            if(array_key_exists('video', $leadActionPage) && $leadActionPage['video'] == 1)
                                            {
                                        ?>
                                                <div class="embed-container">
                                                    @if($leadActionPage['videotype'] == 'youtube')
                                                        <iframe src='//www.youtube.com/embed/{{$leadActionPage['videoid']}}?rel=0' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                                                    @elseif($leadActionPage['videotype'] == 'vimeo')
                                                        <iframe src='//player.vimeo.com/video/{{$leadActionPage['videoid']}}' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                                                    @endif
                                                </div>
                                        <?php
                                            }
                                        ?>

                                        <div class="actual-optin-form-container">
                                            <?php
                                                if(array_key_exists('firstname', $leadActionPage) && $leadActionPage['firstname'] == 1)
                                                {
                                            ?>
                                                <div class="input-icon right margin-top-10">
                                                    <i class="fa fa-user-o"></i>
                                                    <input type="text" class="form-control input-lg app-survey-optin-form-input" placeholder="Enter your first name" id="optin_firstname">
                                                </div>
                                            <?php
                                                }
                                            ?>

                                            <div class="input-icon right margin-top-10">
                                                <i class="fa fa-envelope-o"></i>
                                                <input type="text" class="form-control input-lg app-survey-optin-form-input" placeholder="Enter your email address" id="optin_email">
                                            </div>

                                            @if(array_key_exists('is_segmate_enabled', $leadActionPage) && $leadActionPage['is_segmate_enabled'] == 1 && isset($leadActionPage['segmate_ref_id']))
                                                <div id='{{ $leadActionPage['segmate_ref_id'] }}' style="text-align: center;"></div>
                                            @endif

                                            <input type="hidden" id="response_code" value="{{$response_code}}" />

                                            <div class="continue-button-container text-center">
                                                <button class="continue-button" type="button" id="save-optin-details-button">
                                                    <span>Continue...</span>
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1"></div>
                        </div>


                    </div>
                </div>
            </form>

            <div class="row appSurveyPrivacyPolicyFooterRow">
                <div class="col-md-12">
                    <footer class="footer">
                        <div class="row clearfix">
                            <div class="col-md-12 text-center">
                                <a href="{{url('privacy-policy')}}" target="_blank">Privacy Policy</a>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>

        </div>
        <div class="col-md-2"></div>
    </div>
@endsection


@section('page-level-plugins-js')
    @if(array_key_exists('is_segmate_enabled', $leadActionPage) && $leadActionPage['is_segmate_enabled'] == 1 && isset($leadActionPage['segmate_ref_id']))
        <script type="text/javascript" src="//widget.segmate.io/checkbox/{{ $leadActionPage['segmate_ref_id'] }}.js"></script>
    @endif
@endsection

@section('page-level-scripts-js')

    <script>
        var site_url = '{{url("/")}}';
        var survey_id = '{{$survey->id}}';
    </script>

    <script>
        $(function () {
            $(document).on('click', '#save-optin-details-button', function(e)
            {
                var errorFlag = 0;

                @if(array_key_exists('is_segmate_enabled', $leadActionPage) && $leadActionPage['is_segmate_enabled'] == 1 && isset($leadActionPage['segmate_ref_id']))
                    SgC.confirmOptIn();
                @endif

                var optin_email = $('#optin_email').val().trim();

                if(optin_email == '' || !validateEmail(optin_email))
                {
                    errorFlag = 1;
                    swal('Error', 'Please enter a valid email before submitting', 'error');
                }



                var optin_firstname = '-1';

                if(errorFlag == 0 && $('#optin_firstname').length)
                {
                    if($('#optin_firstname').val().trim() == '')
                    {
                        errorFlag = 1;
                        swal('Error', 'Please enter a first name before submitting', 'error');
                    }
                    else
                    {
                        optin_firstname = $('#optin_firstname').val();
                    }
                }

                var response_code = $('#response_code').val();

                if(errorFlag == 0)
                {
                    pageLoader('show');
                    $.ajax({
                        type: "POST",
                        url: site_url + '/app/add-lead-action-optin-details',
                        data: {'survey_id': survey_id, 'optin_email': optin_email, 'optin_firstname': optin_firstname, 'response_code': response_code, 'choice': '{{ $choicekey }}'},
                        success: function (response)
                        {
                            if (response.status == 'success')
                            {
                                setTimeout(function ()
                                {
                                    protect = false;
                                    window.location.replace(response.redirect_to);
                                }, 2000);
                            }
                            else
                            {
                                pageLoader('hide');
                                swal(response.title, response.message, response.type);
                            }
                        }
                    });
                }
            });

            function validateEmail(email)
            {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }

            function pageLoader(method)
            {
                if (method == 'show')
                {
                    // lock scroll position, but retain settings for later
                    var scrollPosition = [
                        self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
                        self.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
                    ];
                    var html = jQuery('html'); // it would make more sense to apply this to body, but IE7 won't have that
                    html.data('scroll-position', scrollPosition);
                    html.data('previous-overflow', html.css('overflow'));
                    html.css('overflow', 'hidden');
                    window.scrollTo(scrollPosition[0], scrollPosition[1]);


                    $('html, body').animate({scrollTop: 0}, 'slow', function ()
                    {
                    });
                    $('.page-loader').css({height: $(document).height(), display: 'block'});
                }
                else if (method == 'hide')
                {
                    // un-lock scroll position
                    var html = jQuery('html');
                    var scrollPosition = html.data('scroll-position');
                    html.css('overflow', html.data('previous-overflow'));
                    window.scrollTo(scrollPosition[0], scrollPosition[1]);
                    $('.page-loader').css({height: $(document).height(), display: 'none'});
                }
            }
        });
    </script>
@endsection


@section('page-body-end-retargeting-code')
    @if($survey->body_end_retargeting_code != '' && $survey->body_end_retargeting_code != NULL)
        {!! base64_decode($survey->body_end_retargeting_code) !!}
    @endif
@endsection
