app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //CUSTOMER
    when('/user-pkg/user/list', {
        template: '<user-list></user-list>',
        title: 'Users',
    }).
    when('/user-pkg/user/add', {
        template: '<user-form></user-form>',
        title: 'Add User',
    }).
    when('/user-pkg/user/edit/:id', {
        template: '<user-form></user-form>',
        title: 'Edit User',
    });
}]);

app.component('userList', {
    templateUrl: user_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var dataTable = $('#users_list').DataTable({
            "dom": dom_structure,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            processing: true,
            serverSide: true,
            paging: true,
            stateSave: true,
            ajax: {
                url: laravel_routes['getUserList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.user_code = $('#user_code').val();
                    d.user_name = $('#user_name').val();
                    d.mobile_no = $('#mobile_no').val();
                    d.email = $('#email').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'code', name: 'users.code' },
                { data: 'name', name: 'users.name' },
                { data: 'mobile_no', name: 'users.mobile_no' },
                { data: 'email', name: 'users.email' },
            ],
            "initComplete": function(settings, json) {
                $('.dataTables_length select').select2();
            },
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(max + '/ ' + total)
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });

        /* Page Title Appended */
        $('.page-header-content .display-inline-block .data-table-title').html('Customer Channel Groups <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        if (self.hasPermission('add-customer-channel-group')) {
            $('.add_new_button').html(
                '<a href="#!/customer-channel-pkg/customer-channel-group/add" type="button" class="btn btn-secondary">' +
                'Add New' +
                '</a>'
            );
        }
        $('.btn-add-close').on("click", function() {
            $('#users_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#users_list').DataTable().ajax.reload();
        });

        //DELETE
        $scope.deleteUser = function($id) {
            $('#user_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#user_id').val();
            $http.get(
                user_delete_data_url + '/' + $id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'User Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#users_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/user-pkg/user/list');
                }
            });
        }

        //FOR FILTER
        $('#user_code').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#user_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#mobile_no').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#email').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.reset_filter = function() {
            $("#user_name").val('');
            $("#user_code").val('');
            $("#mobile_no").val('');
            $("#email").val('');
            dataTables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('userForm', {
    templateUrl: user_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? user_get_form_data_url : user_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            get_form_data_url
        ).then(function(response) {
            // console.log(response);
            self.user = response.data.user;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.user.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        $scope.btnNxt = function() {}
        $scope.prev = function() {}

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                },
                'username': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'password': {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                },
                'mobile_number': {
                    number: true,
                    minlength: 10,
                    maxlength: 10,
                },
                'mpin': {
                    number: true,
                    minlength: 4,
                    maxlength: 10,
                },
                'otp': {
                    minlength: 4,
                    maxlength: 6,
                },
                'imei': {
                    minlength: 13,
                    maxlength: 15,
                },
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveUser'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: res.message,
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            $location.path('/user-pkg/user/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                $noty = new Noty({
                                    type: 'error',
                                    layout: 'topRight',
                                    text: errors
                                }).show();
                                setTimeout(function() {
                                    $noty.close();
                                }, 3000);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/user-pkg/user/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        $noty = new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'Something went wrong at server',
                        }).show();
                        setTimeout(function() {
                            $noty.close();
                        }, 3000);
                    });
            }
        });
    }
});