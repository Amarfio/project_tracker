var sheetApp = angular.module(
  "sheetApp",
  ["ngRoute", "ngCookies", "ngStorage"],
);

sheetApp.config(
  [
    "$routeProvider",
    "$locationProvider",
    function ($routeProvider, $locationProvider) {
      // use the HTML5 History API
      $locationProvider.html5Mode({
        enabled: true,
        requireBase: false,
      }).hashPrefix("*");

      $routeProvider
        .when("/", {
          templateUrl: "./templates/welcome.html",
          controller: "WelcomeCtrl",
        })
        .when("/login", {
          templateUrl: "./templates/login.html",
          controller: "LoginCtrl",
        })
        .when("/dashboard_p", {
          templateUrl: "./templates/dashboard_p.html",
          controller: "DashboardCtrl",
        })
        .when("/dashboard_test", {
          templateUrl: "./templates/dashboard_tests.html",
          controller: "DashboardCtrl",
        })
        .when("/project_updates", {
          templateUrl: "./templates/project_updates.html",
          controller: "ProjectUpdatesDashboardCtrl",
        })
        .when("/dashboard_t", {
          templateUrl: "./templates/dashboard_t.html",
          controller: "DashboardCtrl_",
        })
        .when("/chat", {
          templateUrl: "./templates/chat.html",
          controller: "ChatCtrl",
        })
        .when("/profile", {
          templateUrl: "./templates/profile.html",
          controller: "ProfileCtrl",
        })
        .when("/add_project", {
          templateUrl: "./templates/add_project.html",
          controller: "AddProjectCtrl",
        })
        .when("/add_change", {
          templateUrl: "./templates/add_change.html",
          controller: "AddChangeCtrl",
        })
        .when("/edit_project/:project_id", {
          templateUrl: "./templates/edit_project.html",
          controller: "EditProjectCtrl",
        })
        .when("/all_projects", {
          templateUrl: "./templates/all_projects.html",
          controller: "ProjectCtrl",
        })
        .when("/my_issues", {
          templateUrl: "./templates/my_issues.html",
          controller: "IssuesCtrl",
        })
        .when("/add_task", {
          templateUrl: "./templates/add_task.html",
          controller: "AddTaskCtrl",
        })
        .when("/add_test", {
          templateUrl: "./templates/add_test.html",
          controller: "AddTestCtrl",
        })
        .when("/edit_task", {
          templateUrl: "./templates/edit_task.html",
          controller: "EditTaskCtrl",
        })
        .when("/edit_user/:user_id", {
          templateUrl: "./templates/edit_user.html",
          controller: "EditUserCtrl",
        })
        .when("/handover", {
          templateUrl: "./templates/handover.html",
          controller: "HandOverCtrl",
        })
        .when("/all_tasks", {
          templateUrl: "./templates/all_tasks.html",
          controller: "AllTasksCtrl",
        })
        .when("/project/:project_id", {
          templateUrl: "./templates/tasks.html",
          controller: "TaskCtrl",
        })
        .when("/task_detail/:task_id", {
          templateUrl: "./templates/task_detail.html",
          controller: "TaskDetailCtrl",
        })
        .when("/test_detail/:test_id", {
          templateUrl: "./templates/test_detail.html",
          controller: "TestDetailCtrl",
        })
        .when("/departments", {
          templateUrl: "./templates/departments.html",
          controller: "DepartmentCtrl",
        })
        .when("/users", {
          templateUrl: "./templates/users.html",
          controller: "UserCtrl",
        })
        .when("/clients", {
          templateUrl: "./templates/clients.html",
          controller: "ClientCtrl",
        })
        .when("/codex", {
          templateUrl: "./templates/codex.html",
          controller: "CodexCtrl",
        })
        .when("/limits", {
          templateUrl: "./templates/limits.html",
          controller: "LimitsCtrl",
        })
        .when("/set_password/:hash", {
          templateUrl: "./templates/set_password.html",
          controller: "SetPasswordCtrl",
        })
        .when("/forgot_password", {
          templateUrl: "./templates/forgot_password.html",
          controller: "forgotPasswordCtrl",
        })
        .when("/log_activity", {
          templateUrl: "./templates/log_activity.html",
          controller: "LogActivityCtrl",
        })
        .when("/_addUser", {
          templateUrl: "./templates/_addUser.html",
        })
        // .when('/side_nav', {
        //     templateUrl: './templates/side_nav.html',
        //     controller: 'SideNavCtrl'
        // })
        .when("/logout", {
          templateUrl: "./templates/logout.html",
          controller: "LogoutCtrl",
        })
        .when("/404", {
          templateUrl: "./templates/404.html",
          // controller: 'UserCtrl'
        })
        .otherwise({
          redirectTo: "/404",
          templateUrl: "./templates/404.html",
        });
    },
  ],
);

sheetApp.service(
  "check_auth",
  function ($location, $http, $localStorage, myConfig) {
    var user_info;

    this.save_auth = function (user_info) {
      var info = $localStorage.user_info = user_info;
      return info;
    };
    this.profile_pic = function (profile_pic) {
      var profile_pic = $localStorage.profile_pic = profile_pic;
      return profile_pic;
    };
    this.signature_pic = function (signature_pic) {
      var signature_pic = $localStorage.signature_pic = signature_pic;
      return signature_pic;
    };
    this.verify_auth = function (auth) {
      if (auth == undefined) {
        return $location.path("/login");
      }
    };

    this.logout = function (user_id) {
      // $localStorage.$reset()
      // return $location.path('/logout')

      var _url = myConfig.url + "/logout.php?user_id=" + user_id;

      $http({
        method: "GET",
        url: _url,
      }).then(function successCallback(response) {
        if (response.data["status"] == "success") {
          $localStorage.$reset();
          return window.location = "login";
          // return $location.path('/login')
        } else {
          Swal.fire({
            type: "error",
            title: response.data["message"],
          });
        }
      });
    };
  },
);

//

sheetApp.service("get_services", function (myConfig, $http) {
  this.get_departments = function (dpt) {
    $http({
      method: "GET",
      url: myConfig.url + "/getCodeDescription.php?init=" + dpt,
    }).then(function successCallback(response) {
      var departments = response.data[0].code_desc;
      // console.log(departments)
      return departments;
    }, function errorCallback(response) {
      // alert("Error. Try Again!");
    });
  };

  this.get_one_code_description = function (code_desc, file) {
    $http.get(myConfig.url + file + code_desc)
      .then(function successCallback(response) {
        var code_description;
        code_description = response.data[0].code_desc;
        console.log(code_description);
        return code_description;

        // var departments = response.data[0].code_desc;
        // console.log(departments) 
        // return departments
      }, function errorCallback(response) {
      });
  };
  // this.get_departments('dpt')
});

sheetApp.service("dataService", function ($http, myConfig) {
  var urlBase = myConfig.url + "/";

  this.get_all_project = function (file) {
    // console.log(urlBase + file);
    return $http.get(urlBase + file);
  };

  this.get_one_code_description = function (code_desc, file) {
    return $http.get(urlBase + file + code_desc)
      .then(function successCallback(response) {
        var code_desc;
        code_desc = response.data[0].code_desc;
      }, function errorCallback(response) {
      });
  };

  // this.insertCustomer = function (cust) {
  //     return $http.post(urlBase, cust);
  // };

  // this.updateCustomer = function (cust) {
  //     return $http.put(urlBase + '/' + cust.ID, cust)
  // };

  // this.deleteCustomer = function (id) {
  //     return $http.delete(urlBase + '/' + id);
  // };

  // this.getOrders = function (id) {
  //     return $http.get(urlBase + '/' + id + '/orders');
  // };
});
