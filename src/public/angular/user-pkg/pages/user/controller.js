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
    }).
    when('/user-pkg/user/view/:id', {
        template: '<user-view></user-view>',
        title: 'View User',
    });
}]);

app.component('userList', {
    templateUrl: user_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $element, $mdSelect) {
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
                url: laravel_routes['getUserPkgList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.name = $('#name').val();
                    d.username = $('#username').val();
                    d.mobile_number = $('#mobile_number').val();
                    d.email = $('#email').val();
                    d.status = $('#status').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'name', name: 'users.name' },
                { data: 'username', name: 'users.username' },
                { data: 'email', name: 'users.email' },
                { data: 'mobile_number', name: 'users.mobile_number' },
                { data: 'roles_count', name: 'roles_count', searchable: false },
                { data: 'status', name: 'status', searchable: false },
            ],
            "initComplete": function(settings, json) {
                $('.dataTables_length select').select2();
                $('#modal-loading').modal('hide');
            },
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total + ' / ' + max)
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });

        /* Page Title Appended */
        $('.page-header-content .display-inline-block .data-table-title').html('Users <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        if (self.hasPermission('add-user')) {
            var addnew_block = $('#add_new_wrap').html();
            $('.page-header-content .alignment-right .add_new_button').html(
                '<a role="button" id="open" data-toggle="modal"  data-target="#modal-user-filter" class="btn btn-img"> <img src="' + image_scr + '" alt="Filter" onmouseover=this.src="' + image_scr1 + '" onmouseout=this.src="' + image_scr + '"></a>' +
                '' + addnew_block + ''
            );
        }
        $('.btn-add-close').on("click", function() {
            $('#users_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#users_list').DataTable().ajax.reload();
        });

        //FOCUS ON SEARCH FIELD
        setTimeout(function() {
            $('div.dataTables_filter input').focus();
        }, 2500);

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
                    custom_noty('success', 'User Deleted Successfully');
                    $('#users_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/user-pkg/user/list');
                }
            });
        }

        //FOR FILTER
        self.status = [
            { id: '', name: 'Select Status' },
            { id: '1', name: 'Active' },
            { id: '0', name: 'Inactive' },
        ];
        $element.find('input').on('keydown', function(ev) {
            ev.stopPropagation();
        });
        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });

        var datatables = $('#users_list').dataTable();
        $('#name').on('keyup', function() {
            datatables.fnFilter();
        });
        $('#username').on('keyup', function() {
            datatables.fnFilter();
        });
        $('#mobile_number').on('keyup', function() {
            datatables.fnFilter();
        });
        $('#email').on('keyup', function() {
            datatables.fnFilter();
        });
        $scope.onSelectedStatus = function(val) {
            $("#status").val(val);
            datatables.fnFilter();
        }
        $scope.reset_filter = function() {
            $("#name").val('');
            $("#username").val('');
            $("#mobile_number").val('');
            $("#email").val('');
            $("#status").val('');
            datatables.fnFilter();
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
            self.role_list = response.data.role_list;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                self.user.role = [];
                angular.forEach(self.user.roles, function(value, key) {
                    self.user.role.push(value.id);
                });
                $scope.changePassword(0);
                if (self.user.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        $scope.changePassword = function(val) {
            if (val == 0) {
                $(".hide_password").hide();
                $("#password").attr('disabled', true);
            } else {
                $(".hide_password").show();
                $("#password").attr('disabled', false);
                $("#password").val('');
            }
        }

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

        //FOCUS ON FIRST INPUT FIELD IN FORM
        $("input:text:visible:first").focus();

        //ROLE VALIDATION 
        $.validator.addMethod("roles", function(value, element) {
            return this.optional(element) || value != '[]';
        }, " This field is required.");

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
                'roles_id': {
                    roles: true,
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
                            custom_noty('success', res.message);
                            $location.path('/user-pkg/user/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                custom_noty('error', errors);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/user-pkg/user/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            }
        });
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('userView', {
    templateUrl: user_view_template_url,
    controller: function($http, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            user_view_data_url + '/' + $routeParams.id
        ).then(function(response) {
            // console.log(response);
            self.user = response.data.user;
            self.action = response.data.action;
            self.roles = [];
            angular.forEach(self.user.roles, function(value, key) {
                self.roles.push(value.name);
            });
            self.user_roles = self.roles.join(", ");
        });
    }
});