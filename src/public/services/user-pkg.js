app.factory('UserSvc', function(RequestSvc) {

    var model = 'user';

    function index(params) {
        return RequestSvc.get('/api/' + model + '/index', params);
    }

    function read(id) {
        return RequestSvc.get('/api/' + model + '/read/' + id);
    }

    function save(params) {
        return RequestSvc.post('/api/' + model + '/save', params);
    }

    function remove(params) {
        return RequestSvc.post('api/' + model + '/delete', params);
    }

    function options(params) {
        return RequestSvc.get('/api/' + model + '/options', params);
    }

    return {
        index: index,
        read: read,
        save: save,
        remove: remove,
        options: options,
    };

});