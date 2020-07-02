
sheetApp.controller('WelcomeCtrl', function ($scope, myConfig,) {
   
    $scope.getYear = function(){
        return current_year = (new Date).getFullYear();
        // console.log((new Date).getFullYear());
    }
});