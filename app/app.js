var sheetApp = angular.module('sheetApp', ['ngRoute']);

sheetApp.config(['$routeProvider'], function ($routeProvider) {
    $routeProvider
    .when('/app/', {
        templateUrl: './templates/dashboard.html'
    })
    .when('/app/dashboard', {
        templateUrl: './templates/dashboard.html',
        controller: 'DashboardCtrl'
    })
    .otherwise({
        redirectTo: './templates/404.html'
    })
});