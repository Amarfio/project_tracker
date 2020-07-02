sheetApp.controller('SideNavCtrl', function ($scope, check_auth, myConfig, $http, $location, $localStorage, $location) {
    check_auth.verify_auth($localStorage.user_info)
    console.log($localStorage.user_info.data);
    $scope.user_info = $localStorage.user_info.data
    $scope.user_role = $scope.user_info.role

    // PROFILE PHOTO
    $scope.profile_pic_true = $localStorage.profile_pic
    $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
    console.log($scope.profile_pic)


    $scope.money = 'hey money come'
    console.log($scope.money);

    // $scope.get_assigner_sideNav = function(){
    //     return [
    //          {
    //              route: '/dashboard',
    //              label: 'Dasboard',
    //              iconStyle: 'ni ni-tv-2 text-primary',

    //          },
    //          {
    //              route: '/all_projects',
    //              label: 'Projects',
    //              iconStyle: 'ni ni-collection text-green',

    //          },
    //          {
    //              route: '/all_tasks',
    //              label: 'Tasks',
    //              iconStyle: 'ni ni-books text-default',

    //          },
    //     ]
    // }

    // $scope.sideNav = [
    //     {
    //         route: '/dashboard',
    //         label: 'Dasboard',
    //         iconStyle: 'ni ni-tv-2 text-primary',
    //         role: ''

    //     },
    //     {
    //         route: '/all_projects',
    //         label: 'Projects',
    //         iconStyle: 'ni ni-collection text-green',
    //         role: ''

    //     },
    //     // {
    //     //     route: '/all_tasks', 
    //     //     label: 'Tasks',
    //     //     iconStyle: 'ni ni-books text-default',
    //     //     role:  ''

    //     // },
    //     {
    //         route: '/add_project',
    //         label: 'Create Project',
    //         iconStyle: 'ni ni-bullet-list-67 text-info',
    //         role: 'admin'

    //     },
    //     {
    //         route: '/departments',
    //         label: 'Departments',
    //         iconStyle: 'ni ni-building text-orange',
    //         role: 'admin'

    //     },
    //     {
    //         route: '/users',
    //         label: 'Users',
    //         iconStyle: 'ni ni-badge text-blue',
    //         role: 'admin'

    //     },
    //     {
    //         route: '/clients',
    //         label: 'Clients',
    //         iconStyle: 'ni ni-paper-diploma text-default',
    //         role: 'admin'

    //     },
    //     {
    //         route: '/codex',
    //         label: 'Codes',
    //         iconStyle: 'ni ni-atom text-red',
    //         role: 'admin'

    //     },
    //     {
    //         route: '/profile',
    //         label: 'Profile',
    //         iconStyle: 'ni ni-single-02 text-yellow',
    //         role: ''

    //     }
    // ]
});