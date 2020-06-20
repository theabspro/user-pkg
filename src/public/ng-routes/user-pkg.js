app.config(['$routeProvider', function($routeProvider) {
    $routeProvider.
    //USER
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