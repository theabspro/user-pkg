@if(config('user-pkg.DEV'))
    <?php $user_pkg_prefix = '/packages/abs/user-pkg/src';?>
@else
    <?php $user_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var user_list_template_url = "{{asset($user_pkg_prefix.'/public/themes/'.$theme.'/user-pkg/user/list.html')}}";
    var user_form_template_url = "{{asset($user_pkg_prefix.'/public/themes/'.$theme.'/user-pkg/user/form.html')}}";
    var user_view_template_url = "{{asset($user_pkg_prefix.'/public/themes/'.$theme.'/user-pkg/user/view.html')}}";
</script>
<script type="text/javascript" src="{{asset($user_pkg_prefix.'/public/ng-routes/user-pkg.js')}}"></script>
<script type="text/javascript" src="{{asset($user_pkg_prefix.'/public/themes/'.$theme.'/user-pkg/user/controller.js')}}"></script>

{{--<script type="text/javascript" src="{{URL::asset($user_pkg_prefix.'/public/angular/user-pkg/pages/user/controller.js?v=2')}}"></script>--}}
