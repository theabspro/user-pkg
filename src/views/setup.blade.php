@if(config('user-pkg.DEV'))
    <?php $user_pkg_prefix = '/packages/abs/user-pkg/src';?>
@else
    <?php $user_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var user_list_template_url = "{{URL::asset($user_pkg_prefix.'/public/angular/user-pkg/pages/user/list.html')}}";
    var user_form_template_url = "{{URL::asset($user_pkg_prefix.'/public/angular/user-pkg/pages/user/form.html')}}";
    var user_view_template_url = "{{URL::asset($user_pkg_prefix.'/public/angular/user-pkg/pages/user/view.html')}}";
</script>
<script type="text/javascript" src="{{URL::asset($user_pkg_prefix.'/public/angular/user-pkg/pages/user/controller.js?v=2')}}"></script>
