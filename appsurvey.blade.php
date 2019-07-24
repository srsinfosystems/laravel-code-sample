@extends('layouts.publicpages')

@section('headertitle')
    Application Survey - {{$survey->name}}
@endsection

@section('facebook-open-graph-section')
    @if($survey->facebook_post_title != '' && $survey->facebook_post_title != NULL)
        <meta property="og:title" content="{!! base64_decode($survey->facebook_post_title) !!}" />
    @endif

    @if($survey->facebook_post_description != '' && $survey->facebook_post_description != NULL)
        <meta property="og:description" content="{!! base64_decode($survey->facebook_post_description) !!}" />
    @endif


    @if($survey->facebook_post_image && $survey->facebook_post_image != '' && $survey->facebook_post_image != 0)
        <meta property="og:image" content="{{ url('survey_images_id/' . $survey->facebook_post_image) }}" />
    @endif
@endsection


@section('page-level-plugins-css')
    @if($survey->appsurvey_bgimage)
        <style>
            .page-content
            {
                background-image: url("{{url('survey_images_id/' . $survey->appsurvey_bgimage)}}") !important;
                background-attachment: fixed !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                background-size: cover !important;
            }
        </style>
    @endif
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

            @if($intro_page)
                <form role="form" id="appsurveyintroform">
                    <div class="form-body">
                        <div class="form-container publicFacingQuestionsContainer center-block">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-10">
                                    <div class="resetCSSToDefault">
                                        <div class="survay-optin-container">

                                            <?php
                                                $intro_page_decoded = unserialize(base64_decode($intro_page->page_info));
                                                if($intro_page_decoded['type'] == 1)
                                                {
                                            ?>
                                                <?php
                                                    if(array_key_exists('headline', $intro_page_decoded))
                                                    {
                                                ?>
                                                    <div class="survay-optin-headline-container text-center">
                                                        {{ucfirst($intro_page_decoded['headline'])}}
                                                    </div>
                                                <?php
                                                    }
                                                ?>

                                                <?php
                                                    if(array_key_exists('subheadline', $intro_page_decoded) && $intro_page_decoded['subheadline'])
                                                    {
                                                ?>
                                                    <div class="survay-optin-subheadline-container text-center">
                                                            {{ucfirst($intro_page_decoded['subheadline'])}}
                                                        </div>
                                                <?php
                                                    }
                                                ?>

                                                <?php
                                                    if(array_key_exists('video', $intro_page_decoded) && $intro_page_decoded['video'] == 1)
                                                    {
                                                ?>
                                                        <div class="embed-container">
                                                            @if($intro_page_decoded['videotype'] == 'youtube')
                                                                <iframe src='//www.youtube.com/embed/{{$intro_page_decoded['videoid']}}?rel=0' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                                                            @elseif($intro_page_decoded['videotype'] == 'vimeo')
                                                                <iframe src='//player.vimeo.com/video/{{$intro_page_decoded['videoid']}}' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                                                            @endif
                                                        </div>
                                                <?php
                                                    }
                                                ?>

                                                <?php
                                                    if(array_key_exists('optin', $intro_page_decoded) && $intro_page_decoded['optin'] == 1)
                                                    {
                                                ?>
                                                    <div class="actual-optin-form-container">
                                                            <?php
                                                                if(array_key_exists('firstname', $intro_page_decoded) && $intro_page_decoded['firstname'] == 1)
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

                                                            @if(array_key_exists('is_segmate_enabled', $intro_page_decoded) && $intro_page_decoded['is_segmate_enabled'] == 1 && isset($intro_page_decoded['segmate_ref_id']))
                                                                    <div id='{{ $intro_page_decoded['segmate_ref_id'] }}' style="text-align: center;"></div>
                                                            @endif


                                                        </div>
                                                <?php
                                                    }
                                                ?>

                                                <div class="continue-button-container text-center">
                                                    <button class="continue-button" type="button" id="save-optin-details-button">
                                                        <span>Continue...</span>
                                                    </button>
                                                </div>
                                            <?php
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1"></div>
                            </div>


                        </div>
                    </div>
                </form>
            @endif


            <form role="form" id="appSurveyForm" data-parsley-validate="">
                <div class="form-body">
                    <div class="form-container publicFacingQuestionsContainer center-block">
                        <div id="logoContainer" @if($survey->appsurvey_topbar_color) style="background-color: #{{$survey->appsurvey_topbar_color}};" @endif>
                            @if(!empty($survey->appsurveylogo))
                                <img src="{{ url('survey_images_id/' . $survey->appsurveylogo . '?width=180&height=75') }}" class="img-responsive">
                            @endif
                        </div>

                        <div id="titleDescContainer">
                            <div id="appSurveyTitleContainer">
                                @if(!empty($survey->appsurveytitle))
                                    {{base64_decode($survey->appsurveytitle)}}
                                @endif
                            </div>
                            <div id="appSurveyDescContainer">
                                @if(!empty($survey->appsurveydesc))
                                    {{base64_decode($survey->appsurveydesc)}}
                                @endif
                            </div>
                        </div>

                        @if(!empty($survey->appsurveylogo) || !empty($survey->appsurveytitle))
                            <hr class="noMarginTopHR" />
                        @endif

                        @if(!$appsurvey_video->isEmpty())
                            <?php
                                $appSurveyVideoDecoded = unserialize(base64_decode($appsurvey_video->first()->page_info));
                            ?>
                            <div id="appSurveyVideoContainer" class="embed-container">
                                @if($appSurveyVideoDecoded['provider'] == 'youtube')
                                    <iframe src='//www.youtube.com/embed/{{$appSurveyVideoDecoded['id']}}?rel=0' frameborder='0' allowfullscreen></iframe>
                                @elseif($appSurveyVideoDecoded['provider'] == 'vimeo')
                                    <iframe src='//player.vimeo.com/video/{{$appSurveyVideoDecoded['id']}}' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                                @endif
                            </div>
                            @if($appSurveyVideoDecoded['type'] == '1')
                                <div id="surveyIntroduction" class="text-center">
                                    Please Fill Out This Questionnaire
                                </div>
                            @elseif($appSurveyVideoDecoded['type'] == '2')
                                <div id="appSurveyVidCTAContainer" class="text-center">
                                    @if($appSurveyVideoDecoded['buttonSize'] == '1')
                                        <button type="button" class="btn btn-circle btn-default appsurveyCTAButton" id="appSurveyCTAButtonActual">{{$appSurveyVideoDecoded['buttonText']}}</button>
                                    @else
                                        <button type="button" class="btn btn-circle btn-default btn-lg appsurveyCTAButton" id="appSurveyCTAButtonActual">{{$appSurveyVideoDecoded['buttonText']}}</button>
                                    @endif
                                </div>
                            @endif
                        @endif


                        <div id="actualSurveyContainer">
                            @php
                                $segmate_contact_info_check = 0;
                            @endphp
                            <div class="sortableAppSurveyContainer">
                                @foreach($app_survey_questions as $question)
                                    <?php $info = unserialize(base64_decode($question->question_info)); ?>
                                    @if($question->question_type=='multiple-choice')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="multiple-choice" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                <div class="nobottomPadding nobottomMargin">
                                                    <?php $choice_count = 0; ?>
                                                    @foreach($info['choices'] as $choice)
                                                        @if(isset($choice['choice']))
                                                        <label class="nomarginMTRadio">
                                                            @if($question->mandatory)
                                                                @if($info['multipleSelection'] == 1)
                                                                    <input type="checkbox" value="{{$choice_count}}" id="choice_{{$choice_count}}" name="question_{{$question->id}}" class="appsurveyCheckboxes" data-parsley-required="true" data-parsley-required-message="Please Select An Option">{{$choice['choice']}}
                                                                @else
                                                                    <input type="radio" value="{{$choice_count}}" id="choice_{{$choice_count}}" name="question_{{$question->id}}" class="appsurveyRadios" data-parsley-required="true" data-parsley-required-message="Please Select An Option">{{$choice['choice']}}
                                                                @endif
                                                            @else
                                                                @if($info['multipleSelection'] == 1)
                                                                    <input type="checkbox" value="{{$choice_count}}" id="choice_{{$choice_count}}" name="question_{{$question->id}}" class="appsurveyCheckboxes" data-parsley-required="false" data-parsley-required-message="Please Select An Option">{{$choice['choice']}}
                                                                @else
                                                                    <input type="radio" value="{{$choice_count}}" id="choice_{{$choice_count}}" name="question_{{$question->id}}" class="appsurveyRadios" data-parsley-required="false" data-parsley-required-message="Please Select An Option">{{$choice['choice']}}
                                                                @endif
                                                            @endif
                                                            <span></span>
                                                        </label>
                                                        <br />
                                                        @else
                                                        <label class="nomarginMTRadio">
                                                            @if($question->mandatory)
                                                                @if($info['multipleSelection'] == 1)
                                                                    <input type="checkbox" value="{{$choice_count}}" id="choice_{{$choice_count}}" name="question_{{$question->id}}" class="appsurveyCheckboxes" data-parsley-required="true" data-parsley-required-message="Please Select An Option">{{$choice}}
                                                                @else
                                                                    <input type="radio" value="{{$choice_count}}" id="choice_{{$choice_count}}" name="question_{{$question->id}}" class="appsurveyRadios" data-parsley-required="true" data-parsley-required-message="Please Select An Option">{{$choice}}
                                                                @endif
                                                            @else
                                                                @if($info['multipleSelection'] == 1)
                                                                    <input type="checkbox" value="{{$choice_count}}" id="choice_{{$choice_count}}" name="question_{{$question->id}}" class="appsurveyCheckboxes" data-parsley-required="false" data-parsley-required-message="Please Select An Option">{{$choice}}
                                                                @else
                                                                    <input type="radio" value="{{$choice_count}}" id="choice_{{$choice_count}}" name="question_{{$question->id}}" class="appsurveyRadios" data-parsley-required="false" data-parsley-required-message="Please Select An Option">{{$choice}}
                                                                @endif
                                                            @endif
                                                            <span></span>
                                                        </label>
                                                        <br />
                                                        @endif
                                                        <?php $choice_count++; ?>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='dropdown')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="dropdown" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>

                                                @if($question->mandatory)
                                                    <select class="form-control nobottomPadding nobottomMargin input-sm appSurveyInput" id="question_{{$question->id}}" data-parsley-required="true" data-parsley-required-message="Please Select An Option From The Dropdown">
                                                @else
                                                    <select class="form-control nobottomPadding nobottomMargin input-sm appSurveyInput" id="question_{{$question->id}}" data-parsley-required="false" data-parsley-required-message="Please Select An Option From The Dropdown">
                                                @endif
                                                        <option value="">{!! $completeLanguageSettings['survey_dropdown_placeholder_text'] !!}</option>
                                                        <?php $choice_count = 0; ?>
                                                        @foreach($info['choices'] as $choice)
                                                            @if(isset($choice['choice']))
                                                                <option value="{{$choice_count}}">{{$choice['choice']}}</option>
                                                            @else
                                                                <option value="{{$choice_count}}">{{$choice}}</option>
                                                            @endif
                                                            <?php $choice_count++; ?>
                                                        @endforeach
                                                    </select>
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='matrix---rating-scale')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="matrix---rating-scale" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                <table class="table nobottomPadding nobottomMargin" id="question_{{$question->id}}">
                                                    <tr>
                                                        <th></th>
                                                        @foreach($info['cols'] as $col)
                                                            <th class="normalFontWeight">{{$col}}</th>
                                                        @endforeach
                                                    </tr>
                                                    <?php $row_count = 0; ?>
                                                    @foreach($info['rows'] as $row)
                                                    <tr class="matrixTr">
                                                        <th class="normalFontWeight">{{$row}}</th>
                                                        <?php $col_count = 0; ?>
                                                        @foreach($info['cols'] as $col)
                                                            <td>
                                                                @if($question->mandatory)
                                                                    <input type="radio" value="{{$col_count}}" name="question_{{$question->id}}_row_{{$row_count}}" data-parsley-required="true" data-parsley-required-message="Please Select A Rating For Each Of The Rows"/>
                                                                @else
                                                                    <input type="radio" value="{{$col_count}}" name="question_{{$question->id}}_row_{{$row_count}}" data-parsley-required="false" data-parsley-required-message="Please Select A Rating For Each Of The Rows"/>
                                                                @endif
                                                            </td>
                                                            <?php $col_count++; ?>
                                                        @endforeach
                                                    </tr>
                                                    <?php $row_count++; ?>
                                                    @endforeach
                                                </table>
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='ranking')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="ranking" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group" id="question_{{$question->id}}">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                <?php $row_count = 0; ?>
                                                @foreach($info['rows'] as $row)
                                                    <div class="form-group row noMarginFormGroup">
                                                        <div class="col-md-3 control-label">
                                                            <label>{{$row}}</label>
                                                        </div>
                                                        <div class="col-md-9">
                                                            @if($question->mandatory)
                                                                <select class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$row_count}}" data-parsley-required="true" data-parsley-required-message="Please Select A Ranking For each For Each Of The Rows">
                                                            @else
                                                                <select class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$row_count}}" data-parsley-required="false" data-parsley-required-message="Please Select A Ranking For each For Each Of The Rows">
                                                            @endif
                                                                    <option value="">Select From The Drop Down</option>
                                                                    @for ($i = 1; $i <= count($info['rows']); $i++)
                                                                        <option value="{{$i-1}}">{{$i}}</option>
                                                                    @endfor
                                                                </select>
                                                        </div>
                                                    </div>
                                                    <?php $row_count++; ?>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='rating')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="rating" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                @if($question->mandatory)
                                                    <div class="ratingControlClass nobottomPadding nobottomMargin appSurveyInput" id="question_{{$question->id}}">
                                                        <input type="hidden" id="hiddenValidationRatingControl_{{$question->id}}" value="-1" data-parsley-raty="1">
                                                    </div>
                                                @else
                                                    <div class="ratingControlClass nobottomPadding nobottomMargin appSurveyInput" id="question_{{$question->id}}">
                                                        <input type="hidden" id="hiddenValidationRatingControl_{{$question->id}}" value="-1">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='opinion')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="opinion" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                <div class="nobottomPadding nobottomMargin appSurveyInput opinionScale" id="question_{{$question->id}}">
                                                    <ul id="opinionScaleContainer_{{$question->id}}">
                                                        @for($opinionScaleCounter = 1; $opinionScaleCounter <= intval($info['steps']); $opinionScaleCounter++)
                                                            <li class="opinionScaleBox" data-score-value="{{$opinionScaleCounter}}"><a>{{$opinionScaleCounter}}</a></li>
                                                        @endfor
                                                    </ul>
                                                    @if($question->mandatory)
                                                        <input type="hidden" id="hiddenValidationOpinionControl_{{$question->id}}" value="-1" data-parsley-opinionscale="1">
                                                    @else
                                                        <input type="hidden" id="hiddenValidationOpinionControl_{{$question->id}}" value="-1">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='single-textbox')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="single-textbox" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                @if($question->mandatory)
                                                    <input type="text" class="form-control nobottomPadding nobottomMargin input-sm appSurveyInput" id="question_{{$question->id}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Answer Before Submitting" />
                                                @else
                                                    <input type="text" class="form-control nobottomPadding nobottomMargin input-sm appSurveyInput" id="question_{{$question->id}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Answer Before Submitting" />
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='multiple-textbox')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="multiple-textbox" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group" id="question_{{$question->id}}">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                <?php $row_count = 0; ?>
                                                @foreach($info['rows'] as $row)
                                                    <div class="form-group noMarginFormGroup multipleTextBoxRow">
                                                        <label>{{$row}}</label>
                                                        @if($question->mandatory)
                                                            <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$row_count}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Answer Before Submitting" />
                                                        @else
                                                            <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$row_count}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Answer Before Submitting" />
                                                        @endif
                                                        <?php $row_count++; ?>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='comment-box')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="comment-box" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                @if($question->mandatory)
                                                    <textarea type="text" class="form-control nobottomPadding nobottomMargin input-sm" id="question_{{$question->id}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Answer Before Submitting"></textarea>
                                                @else
                                                    <textarea type="text" class="form-control nobottomPadding nobottomMargin input-sm" id="question_{{$question->id}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Answer Before Submitting"></textarea>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='contact-information')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="contact-information" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group" id="question_{{$question->id}}">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                <?php $control_row_count = 0; ?>
                                                @foreach($info['controls'] as $control)
                                                    @if($control['type'] == 'segmate' && $control['checkboxrefid'] != '')
                                                        @php
                                                            $segmate_contact_info_check = 1;
                                                        @endphp
                                                        <div class="form-group noMarginFormGroup">
                                                            <div id='{{ $control['checkboxrefid'] }}'></div>
                                                            <script type="text/javascript" src="//widget.segmate.io/checkbox/{{ $control['checkboxrefid'] }}.js"></script>
                                                        </div>
                                                    @elseif($control['type'] != 'address')
                                                        <div class="form-group noMarginFormGroup contactControlContainer" data-contact-group-type="normal-contact-group" data-contact-control-row="{{$control_row_count}}">
                                                            <label>
                                                                @if($control['mandatory'])
                                                                    <div class="mandatoryStar">*</div>
                                                                @endif
                                                                {{$control['label']}}
                                                            </label>
                                                            @if($control['mandatory'] && $question->mandatory)
                                                                @if($control['type'] == 'name')
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Name Before Submitting"/>
                                                                @elseif($control['type'] == 'email')
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Email Before Submitting" data-parsley-type="email" data-parsley-type-message="Please Enter A Valid Email Address"/>
                                                                @elseif($control['type'] == 'company')
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Company Before Submitting"/>
                                                                @elseif($control['type'] == 'phone')
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Phone Number Before Submitting"/>
                                                                @endif
                                                            @else
                                                                @if($control['type'] == 'name')
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Name Before Submitting"/>
                                                                @elseif($control['type'] == 'email')
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Email Before Submitting" data-parsley-type="email" data-parsley-type-message="Please Enter A Valid Email Address"/>
                                                                @elseif($control['type'] == 'company')
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Company Before Submitting"/>
                                                                @elseif($control['type'] == 'phone')
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Phone Number Before Submitting"/>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="form-group noMarginFormGroup contactControlContainer" data-contact-group-type="address-contact-group" data-contact-control-row="{{$control_row_count}}">
                                                            <div class="form-group">
                                                                <label>
                                                                    @if($control['addressmandatory'])
                                                                        <div class="mandatoryStar">*</div>
                                                                    @endif
                                                                    {{$control['addresslabel']}}
                                                                </label>
                                                                @if($control['addressmandatory'] && $question->mandatory)
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_0" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Address Before Submitting"/>
                                                                @else
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_0" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Address Before Submitting"/>
                                                                @endif
                                                            </div>
                                                            <div class="form-group">
                                                                <label>
                                                                    @if($control['address2mandatory'])
                                                                        <div class="mandatoryStar">*</div>
                                                                    @endif
                                                                    {{$control['address2label']}}
                                                                </label>
                                                                @if($control['address2mandatory'] && $question->mandatory)
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_1" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Address Before Submitting"/>
                                                                @else
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_1" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Address Before Submitting"/>
                                                                @endif
                                                            </div>
                                                            <div class="form-group">
                                                                <label>
                                                                    @if($control['citymandatory'])
                                                                        <div class="mandatoryStar">*</div>
                                                                    @endif
                                                                    {{$control['citylabel']}}
                                                                </label>
                                                                @if($control['citymandatory'] && $question->mandatory)
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_2" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your City Before Submitting"/>
                                                                @else
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_2" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your City Before Submitting"/>
                                                                @endif
                                                            </div>
                                                            <div class="form-group">
                                                                <label>
                                                                    @if($control['statemandatory'])
                                                                        <div class="mandatoryStar">*</div>
                                                                    @endif
                                                                    {{$control['statelabel']}}
                                                                </label>
                                                                @if($control['statemandatory'] && $question->mandatory)
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_3" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your State Before Submitting"/>
                                                                @else
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_3" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your State Before Submitting"/>
                                                                @endif
                                                            </div>
                                                            <div class="form-group">
                                                                <label>
                                                                    @if($control['zipmandatory'])
                                                                        <div class="mandatoryStar">*</div>
                                                                    @endif
                                                                    {{$control['ziplabel']}}
                                                                </label>
                                                                @if($control['zipmandatory'] && $question->mandatory)
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_4" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Zipcode Before Submitting"/>
                                                                @else
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_4" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Zipcode Before Submitting"/>
                                                                @endif
                                                            </div>
                                                            <div class="form-group">
                                                                <label>
                                                                    @if($control['countrymandatory'])
                                                                        <div class="mandatoryStar">*</div>
                                                                    @endif
                                                                    {{$control['countrylabel']}}
                                                                </label>
                                                                @if($control['countrymandatory'] && $question->mandatory)
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_5" data-contact-control-type="{{$control['type']}}" data-parsley-required="true" data-parsley-required-message="Please Enter Your Country Before Submitting"/>
                                                                @else
                                                                    <input type="text" class="form-control input-sm appSurveyInput" id="question_{{$question->id}}_row_{{$control_row_count}}_5" data-contact-control-type="{{$control['type']}}" data-parsley-required="false" data-parsley-required-message="Please Enter Your Country Before Submitting"/>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif

                                                <?php $control_row_count++; ?>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='date-time')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="date-time" data-question-mandatory="{{$question->mandatory}}">
                                            <div class="form-group">
                                                <label class="bold questionLabel">
                                                    @if($question->mandatory)
                                                        <div class="mandatoryStar">*</div>
                                                    @endif
                                                    {{$info['question']}}
                                                </label>
                                                @if($info['type'] == 'date-time')
                                                    <div class="form-group date-controls noMarginFormGroup" id="question_{{$question->id}}" data-date-control-type="date-time">
                                                        <div>
                                                            <div class="date-control">
                                                                <label>DD</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control date input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Date Before Submitting" data-parsley-range="[1,31]" data-parsley-range-message="Please Enter A Valid Date" />
                                                                @else
                                                                    <input type="text" class="form-control date input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Date Before Submitting" data-parsley-range="[1,31]" data-parsley-range-message="Please Enter A Valid Date" />
                                                                @endif
                                                            </div>
                                                            <div class="date-control">
                                                                <label>MM</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control month input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Month Before Submitting" data-parsley-range="[1,12]" data-parsley-range-message="Please Enter A Valid Month" />
                                                                @else
                                                                    <input type="text" class="form-control month input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Month Before Submitting" data-parsley-range="[1,12]" data-parsley-range-message="Please Enter A Valid Month" />
                                                                @endif
                                                            </div>
                                                            <div class="date-control">
                                                                <label>YYYY</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control year input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Year Before Submitting" data-parsley-range="[0,10000]" data-parsley-range-message="Please Enter A Valid Year" />
                                                                @else
                                                                    <input type="text" class="form-control year input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Year Before Submitting" data-parsley-range="[0,10000]" data-parsley-range-message="Please Enter A Valid Year" />
                                                                @endif
                                                            </div>
                                                            <div class="date-control">
                                                                <label>HH</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control hour input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Hour Before Submitting" data-parsley-range="[00,23]" data-parsley-range-message="Please Enter A Valid Hour" />
                                                                @else
                                                                    <input type="text" class="form-control hour input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Hour Before Submitting" data-parsley-range="[00,23]" data-parsley-range-message="Please Enter A Valid Hour" />
                                                                @endif
                                                            </div>
                                                            <div class="date-control">
                                                                <label>MM</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control minute input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Minutes Before Submitting" data-parsley-range="[00,59]" data-parsley-range-message="Please Enter A Valid Minute" />
                                                                @else
                                                                    <input type="text" class="form-control minute input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Minutes Before Submitting" data-parsley-range="[00,59]" data-parsley-range-message="Please Enter A Valid Minute" />
                                                                @endif
                                                            </div>
                                                            <div class="date-control am-pm-drop">
                                                                <label>AM/PM</label>
                                                                @if($question->mandatory)
                                                                    <select class="form-control am-pm input-sm" data-parsley-required="true" data-parsley-required-message="Please Select Either AM Or PM Before Submitting" >
                                                                @else
                                                                    <select class="form-control am-pm input-sm" data-parsley-required="false" data-parsley-required-message="Please Select Either AM Or PM Before Submitting" >
                                                                @endif
                                                                        <option value=""></option>
                                                                        <option value="am">AM</option>
                                                                        <option value="pm">PM</option>
                                                                    </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($info['type'] == 'date')
                                                    <div class="form-group date-controls noMarginFormGroup" id="question_{{$question->id}}" data-date-control-type="date">
                                                        <div>
                                                            <div class="date-control">
                                                                <label>DD</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control date input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Date Before Submitting" data-parsley-range="[1,31]" data-parsley-range-message="Please Enter A Valid Date" />
                                                                @else
                                                                    <input type="text" class="form-control date input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Date Before Submitting" data-parsley-range="[1,31]" data-parsley-range-message="Please Enter A Valid Date" />
                                                                @endif
                                                            </div>
                                                            <div class="date-control">
                                                                <label>MM</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control month input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Month Before Submitting" data-parsley-range="[1,12]" data-parsley-range-message="Please Enter A Valid Month" />
                                                                @else
                                                                    <input type="text" class="form-control month input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Month Before Submitting" data-parsley-range="[1,12]" data-parsley-range-message="Please Enter A Valid Month" />
                                                                @endif
                                                            </div>
                                                            <div class="date-control">
                                                                <label>YYYY</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control year input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Year Before Submitting" data-parsley-range="[0,10000]" data-parsley-range-message="Please Enter A Valid Year" />
                                                                @else
                                                                    <input type="text" class="form-control year input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Year Before Submitting" data-parsley-range="[0,10000]" data-parsley-range-message="Please Enter A Valid Year" />
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif ($info['type'] == 'time')
                                                    <div class="form-group date-controls noMarginFormGroup" id="question_{{$question->id}}" data-date-control-type="time">
                                                        <div>
                                                            <div class="date-control">
                                                                <label>HH</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control hour input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Hour Before Submitting" data-parsley-range="[00,23]" data-parsley-range-message="Please Enter A Valid Hour" />
                                                                @else
                                                                    <input type="text" class="form-control hour input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Hour Before Submitting" data-parsley-range="[00,23]" data-parsley-range-message="Please Enter A Valid Hour" />
                                                                @endif
                                                            </div>
                                                            <div class="date-control">
                                                                <label>MM</label>
                                                                @if($question->mandatory)
                                                                    <input type="text" class="form-control minute input-sm" data-parsley-required="true" data-parsley-required-message="Please Enter Minutes Before Submitting" data-parsley-range="[00,59]" data-parsley-range-message="Please Enter A Valid Minute" />
                                                                @else
                                                                    <input type="text" class="form-control minute input-sm" data-parsley-required="false" data-parsley-required-message="Please Enter Minutes Before Submitting" data-parsley-range="[00,59]" data-parsley-range-message="Please Enter A Valid Minute" />
                                                                @endif
                                                            </div>
                                                            <div class="date-control am-pm-drop">
                                                                <label>AM/PM</label>
                                                                @if($question->mandatory)
                                                                    <select class="form-control am-pm input-sm" data-parsley-required="true" data-parsley-required-message="Please Select Either AM Or PM Before Submitting" >
                                                                @else
                                                                    <select class="form-control am-pm input-sm" data-parsley-required="false" data-parsley-required-message="Please Select Either AM Or PM Before Submitting" >
                                                                @endif
                                                                        <option value=""></option>
                                                                        <option value="am">AM</option>
                                                                        <option value="pm">PM</option>
                                                                    </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='text')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="text">
                                            <div class="form-group">
                                                <label class="bold nobottomPadding nobottomMargin">{{$info['question']}}</label>
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='image')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="image">
                                            <div class="form-group">
                                                <label class="bold questionLabel">{{$info['question']}}</label>
                                                <img src="{{ url('survey_images_id/' . $info['image_id']) }}" title="{{$info['question']}}" alt="{{$info['question']}}" class="img-responsive nobottomPadding nobottomMargin"/>
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='text-a-b-test')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="text-a-b-test">
                                            <div class="form-group">
                                                <label class="bold">{{$info['question']}}</label>
                                                <?php $count = 1; ?>
                                                @foreach($info['texts'] as $text)
                                                <div class="form-group">
                                                    <label>{{$count . '. ('. $text['percentage'].'%)'}}</label>
                                                    <label class="bold">{{$text['text']}}</label>
                                                </div>
                                                <?php $count++; ?>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='image-a-b-test')
                                        <div class="questionClass clearfix" data-question-id="{{$question->id}}" data-question-type="image-a-b-test">
                                            <div class="form-group">
                                                <?php $count = 1; ?>
                                                @foreach($info['images'] as $image)
                                                    <div class="form-group">
                                                        <label>{{$count . '. ('.$image['percentage'].'%)'}}</label>
                                                        <label class="bold">{{$image['image_label']}}</label>
                                                    </div>
                                                    <img src="{{$image['image']}}" title="{{$image['image_label']}}" alt="{{$image['image_label']}}" class="img-responsive"/>
                                                    <?php $count++; ?>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($question->question_type=='intro-image')
                                    @elseif($question->question_type=='intro-title')
                                    @elseif($question->question_type=='intro-description')
                                    @endif
                                @endforeach
                            </div>


                            <div class="row" id="appSurveySubmitButtonContainer">
                                <div class="col-md-4"></div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary center-block btn-block" id="surveySubmit" @if($survey->appsurvey_button_color) style="background-color: #{{$survey->appsurvey_button_color}}; border-color: #{{$survey->appsurvey_button_color}}" @endif>
                                        {!! $completeLanguageSettings['survey_submit_button_text'] !!}
                                    </button>
                                </div>
                                <div class="col-md-4"></div>
                            </div>
                        </div>


                    </div>
                </div>
            </form>

            @if(!$appsurvey_footer->isEmpty())
                <div class="row" id="appSurveyFooterContainer">
                    <div class="col-md-1"></div>
                    <div class="col-md-10 text-center" id="appSurveyFooterHTML">
                        <?php echo base64_decode($appsurvey_footer->first()->page_info); ?>
                    </div>
                    <div class="col-md-1">
                    </div>
                </div>
            @endif

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
    <script src="{{ asset('assets/global/plugins/parsley/parsley.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/global/plugins/jquery.raty-fa.js') }}" type="text/javascript"></script>
    @if($intro_page && array_key_exists('is_segmate_enabled', $intro_page_decoded) && $intro_page_decoded['is_segmate_enabled'] == 1 && isset($intro_page_decoded['segmate_ref_id']))
        <script type="text/javascript" src="//widget.segmate.io/checkbox/{{$intro_page_decoded['segmate_ref_id']}}.js"></script>
    @endif
@endsection

@section('page-level-scripts-js')
    <script>
        var site_url = '{{url("/")}}';
        var survey_id = '{{$survey->id}}';
        var sessionid = '{{$sessionid}}';
        var sessionip = '{{$sessionip}}';
        var final_optin_email = '{{ $intro_email }}';
        var final_optin_name = '{{ $intro_name }}';
        var final_optin_phone = '{{ $intro_phone }}';




        @if( Request::get('ev_aff_id') && Request::get('tracking_id'))
            var ev_aff_id = "{{ Request::get('ev_aff_id') }}";
            var ev_tracking_id = "{{ Request::get('tracking_id') }}";
        @else
            var ev_aff_id = null;
            var ev_tracking_id = null;
        @endif

        @if( Request::get('pageSubmissioncode'))
            var page_submission_code = "{{ Request::get('pageSubmissioncode') }}";
        @else
            var page_submission_code = null;
        @endif


        @if($segmate_contact_info_check == 1)
            var is_segmate_enabled_contact_form = 1;
        @else
            var is_segmate_enabled_contact_form = 0;
        @endif

    </script>

    <script>
        $(function ()
        {
            @if(!$appsurvey_video->isEmpty())
                @if($appSurveyVideoDecoded['type'] == '2')
                    $('#appSurveyCTAButtonActual').css('background-color',"{{$appSurveyVideoDecoded['buttonColor']}}");
                    $('#appSurveyCTAButtonActual').css('color',"{{$appSurveyVideoDecoded['fontColor']}}");
                    $('#actualSurveyContainer').hide();
                    @if(array_key_exists('timing', $appSurveyVideoDecoded))
                        $('#appSurveyCTAButtonActual').hide();
                        <?php
                            $delayTimingMilliseconds = $appSurveyVideoDecoded['timing'] * 1000;
                        ?>
                        $('#appSurveyCTAButtonActual').delay({{$delayTimingMilliseconds}}).fadeIn("1000");
                    @endif
                @endif
            @endif

            $(document).on("click", "#appSurveyCTAButtonActual", function (e)
            {
                e.preventDefault();
                $('#appSurveyVideoContainer').empty();
                $('#appSurveyVideoContainer').fadeOut("1000");
                $('#appSurveyVidCTAContainer').fadeOut("1000");
                $('#actualSurveyContainer').fadeIn("3000");
            });


            @if($intro_page)
                $('#appSurveyForm').hide();
            @endif

            $(document).on("click", "#save-optin-details-button", function (e)
            {
                if($('.actual-optin-form-container').length)
                {
                    @if($intro_page && array_key_exists('is_segmate_enabled', $intro_page_decoded) && $intro_page_decoded['is_segmate_enabled'] == 1 && isset($intro_page_decoded['segmate_ref_id']))
                        SgC.confirmOptIn();
                    @endif


                    var errorFlag = 0;

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

                    if(errorFlag == 0)
                    {
                        final_optin_email = optin_email;
                        final_optin_name = optin_firstname;
                        $('.survay-optin-container').html('');
                        $('#appsurveyintroform').fadeOut("1000");
                        $('#appSurveyForm').fadeIn("3000");
                    }

                }
                else
                {
                    $('.survay-optin-container').html('');
                    $('#appsurveyintroform').fadeOut("1000");
                    $('#appSurveyForm').fadeIn("3000");
                }

            });

            function validateEmail(email)
            {
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }

            @foreach($app_survey_questions as $question)
                @if($question->question_type=='rating')
                    <?php
                        $info = unserialize(base64_decode($question->question_info));
                        $steps = $info['steps'];
                        $shape = $info['shape'];
                        $questionId = $question->id;
                    ?>

                    var starShapesArray = getIconStrings('{{$shape}}');
                    var numberOfSteps = parseInt('{{$steps}}');

                    $('#question_{{$questionId}}').raty({
                        starOff: starShapesArray[0],
                        starOn: starShapesArray[1],
                        number: numberOfSteps,
                        click: function(score, evt)
                        {
                            var questionIdForRatingControl = $(this).attr('id').substring(9);
                            $('#hiddenValidationRatingControl_' + questionIdForRatingControl).val(score);
                        }
                    });
                @endif
            @endforeach


            function getIconStrings(shape)
            {
                var starOffString = 'fa fa-2x fa-star-o';
                var starOnString = 'fa fa-2x fa-star';

                switch(shape)
                {
                    case '1':
                        starOffString = 'fa fa-2x fa-star-o';
                        starOnString = 'fa fa-2x fa-star';
                        break;
                    case '2':
                        starOffString = 'fa fa-2x fa-heart-o';
                        starOnString = 'fa fa-2x fa-heart';
                        break;
                    case '3':
                        starOffString = 'fa fa-2x fa-circle-o';
                        starOnString = 'fa fa-2x fa-circle';
                        break;
                    case '4':
                        starOffString = 'fa fa-2x fa-square-o';
                        starOnString = 'fa fa-2x fa-square';
                        break;
                    case '5':
                        starOffString = 'fa fa-2x fa-bell-o';
                        starOnString = 'fa fa-2x fa-bell';
                        break;
                    case '6':
                        starOffString = 'fa fa-2x fa-thumbs-o-up';
                        starOnString = 'fa fa-2x fa-thumbs-up';
                        break;
                    case '7':
                        starOffString = 'fa fa-2x fa-thumbs-o-down';
                        starOnString = 'fa fa-2x fa-thumbs-down';
                        break;
                    case '8':
                        starOffString = 'fa fa-2x fa-comment-o';
                        starOnString = 'fa fa-2x fa-comment';
                        break;
                    case '9':
                        starOffString = 'fa fa-2x fa-sticky-note-o';
                        starOnString = 'fa fa-2x fa-sticky-note';
                        break;
                    case '10':
                        starOffString = 'fa fa-2x fa-trash-o';
                        starOnString = 'fa fa-2x fa-trash';
                        break;
                    case '11':
                        starOffString = 'fa fa-2x fa-user-o';
                        starOnString = 'fa fa-2x fa-user';
                        break;
                }

                var starStrings = [];
                starStrings[0] = starOffString;
                starStrings[1] = starOnString;

                return starStrings;
            }
        });

    </script>

    <script src="{{ asset('assets/custom/js/appsurveypage.js?v=' . CacheBuster::appVersion()) }}" type="text/javascript"></script>
    <script>
        $(function () {
            AppSurvey.init();
        });
    </script>

@endsection


@section('page-body-end-retargeting-code')
    @if($survey->body_end_retargeting_code != '' && $survey->body_end_retargeting_code != NULL)
        {!! base64_decode($survey->body_end_retargeting_code) !!}
    @endif
@endsection
