sheetApp.controller('EditUserCtrl', function (
    $scope, 
    $http,
    $routeParams, 
    check_auth, 
    myConfig, 
    $location, 
    $localStorage) {

  check_auth.verify_auth($localStorage.user_info)
  console.log($localStorage.user_info.data);
  $scope.user_info = $localStorage.user_info.data

  // PROFILE PHOTO
  $scope.profile_pic_true = $localStorage.profile_pic
  $scope.profile_pic = myConfig.file_url + $scope.profile_pic_true
  console.log($scope.profile_pic)

  //logout funtion 
  $scope.logout = function () {
      check_auth.logout()
  }
  // end logout function 

  //code to get the user details by id
  $scope.newUser_id =  $routeParams.user_id


  //get user details using the user's id
  $scope.get_one_user = function () {
    $http({
      method: "GET",
      url: myConfig.url + "/getOneUser.php?user_id=" + $scope.newUser_id
    }).then(function successCallback(response) {
      $scope.user = response.data['data'];
      //   $scope.user = response;
      console.log("user details here: ");
      console.log($scope.user);
    });
  }

  //call the method 
  $scope.get_one_user()



  $scope.name = 'Ampah';

  $scope.get_all_countries = function () {
      $http({
          method: "GET",
          url: "https://restcountries.eu/rest/v2/all"
      }).then(
          function successCallback(response) {
              $scope.countries = response.data
              console.log($scope.countries);
          },
          function errorCallback(response) {
              // alert("Error. Try Again!");
          }
      );
  }
  $scope.get_all_countries()


  $scope.get_departments = function (dpt) {
      $http({
          method: 'GET',
          url: myConfig.url + '/getCodeDescription.php?init=' + dpt

      }).then(function successCallback(response) {

          $scope.departments = response.data[0].code_desc;
          console.log($scope.departments)

      }, function errorCallback(response) {

          // alert("Error. Try Again!");

      });
  }
  $scope.get_departments('dpt')

  $scope.get_roles = function (role) {
      $http({
          method: 'GET',
          url: myConfig.url + '/getCodeDescription.php?init=' + role

      }).then(function successCallback(response) {

          $scope.roles = response.data[0].code_desc;
          console.log($scope.roles)

      }, function errorCallback(response) {

          alert("Error. Try Again!");

      });
  }
  $scope.get_roles('rol')


//   $scope.get_all_users = function () {
//       $http({
//           method: 'GET',
//           url: myConfig.url + '/getAllUsers.php'

//       }).then(function successCallback(response) {

//           $scope.all_users = response.data;
//           console.log($scope.all_users)

//       }, function errorCallback(response) {

//           alert("Error. Try Again!");

//       });
//   }
//   $scope.get_all_users()

  // $scope.is_manager = 0

//   $scope.addUser = function (first_name_, last_name_, username_, gender_, email_, phone_number_, country_, city_, postal_address_, department_, is_manager_, role_, can_approve_) {
//       console.log(first_name_);
//       console.log(last_name_);
//       console.log(username_);
//       console.log(gender_);
//       console.log(email_);
//       console.log(phone_number_);
//       console.log(country_);
//       console.log(city_);
//       console.log(postal_address_);
//       console.log(department_);
//       console.log(is_manager_);
//       console.log(role_);
//       console.log(can_approve_);


//       var data = {
//           first_name: first_name_,
//           last_name: last_name_,
//           username: username_,
//           gender: gender_,
//           dept_id: department_,
//           role_id: role_,
//           can_approve: can_approve_,
//           is_dpt_head: is_manager_,
//           email: email_,
//           phone: phone_number_,
//           country: country_,
//           city: city_,
//           postal_addr: postal_address_
//       }


//       Swal.queue([{
//           title: 'Updating User ...  ',
//           showLoaderOnConfirm: true,
//           onBeforeOpen: () => {
//               Swal.showLoading()
//           },
//           showLoaderOnConfirm: true,
//       }])

//       //$http POST function
//       $http({

//           method: 'POST',
//           url: myConfig.url + '/createUser.php',
//           data: data

//       }).then(function successCallback(response) {
//           $res = response.data
//           console.log($res.status)
//           console.log($res);

//           if ($res.status == 'success') {

//               Swal.fire({
//                   type: 'success',
//                   title: $res.status,
//                   text: $res.message
//               })

//               //    Swal.fire($res.status, "success");
//               setTimeout(() => {
//                   $scope.get_all_users()
//                   $('#add_user_row').hide();
//                   $('#show_user_row').show();
//                   $('#form_add_user')[0].reset();
//                   $scope.is_manager = 0
//                   $scope.can_approve_ = 0
//               }, 2000);
//           } else {
//               Swal.fire({
//                   type: 'error',
//                   title: $res.status,
//                   text: $res.message,
//               })

//               // Swal.fire(
//           }

//       }, function errorCallback(response) {

//           Swal.fire({
//               type: 'warning',
//               title: 'Sorry',
//               text: 'Something went wrong',
//           })

//       });




//   }



});