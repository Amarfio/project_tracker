sheetApp.controller(
    "TaskDetailsCtrl",
    function ($scope, $http, $location, myConfig, $timeout) {
      // Initialize scope variables
      $scope.user_info = {
        username: "Guest",
        role: "developer",
        gender: "Male",
        dept_id: 1,
      };
      $scope.profile_pic_true = null;
      $scope.profile_pic = null;
      $scope.tasks = [];
      $scope.department = $location.search().department || "Unknown";
      $scope.start_date = $location.search().start_date || "";
      $scope.end_date = $location.search().end_date || "";
      $scope.metric = $location.search().metric || "";
  
      // Format dates for display
      $scope.formatDisplayDate = function (dateString) {
        if (!dateString) return "";
        var date = new Date(dateString);
        return date.toLocaleDateString("en-US", {
          year: "numeric",
          month: "short",
          day: "numeric",
        });
      };
  
      $scope.dateRangeDisplay =
        $scope.formatDisplayDate($scope.start_date) +
        " to " +
        $scope.formatDisplayDate($scope.end_date);
  
      // Metric display filter
      $scope.metricDisplay = function () {
        var metricMap = {
          previous_outstanding: "Previous Period Outstanding Tasks",
          new_tasks: "New Tasks",
          total_completed: "Total Completed",
          oldest_outstanding: "Oldest Outstanding",
          tasks_exceeded: "Tasks Exceeded",
          avg_days_to_complete: "Average Days to Complete",
        };
        return metricMap[$scope.metric] || $scope.metric;
      };
  
      // Fetch task details
      $scope.fetchTaskDetails = function () {
        $http
          .get("apiSheet/get_task_details.php", {
            params: {
              department: $scope.department,
              start_date: $scope.start_date,
              end_date: $scope.end_date,
              metric: $scope.metric,
            },
          })
          .then(
            function (response) {
              console.log("Task Details Response:", response.data);
              if (response.data.success) {
                $scope.tasks = response.data.tasks;
              } else {
                console.error(
                  "Error fetching task details:",
                  response.data.message,
                );
                $scope.tasks = [];
              }
            },
            function (error) {
              console.error("HTTP error:", error);
              $scope.tasks = [];
            },
          );
      };
  
      // Logout function (placeholder)
      $scope.logout = function () {
        alert("Logout clicked (implement later)");
      };
  
      // Initialize controller
      $scope.init = function () {
        // Set profile picture based on gender
        if ($scope.user_info.profile_pic) {
          $scope.profile_pic_true = true;
          $scope.profile_pic = $scope.user_info.profile_pic;
        } else {
          $scope.profile_pic_true = null;
        }
        // Fetch task details on page load
        $scope.fetchTaskDetails();
      };
  
      // Call initialization
      $scope.init();
  
  
    },
  );
  
  // Filter for metric display
  sheetApp.filter("metricDisplay", function () {
    return function (metric) {
      var metricMap = {
        previous_outstanding: "Previous Period Outstanding Tasks",
        new_tasks: "New Tasks",
        total_completed: "Total Completed",
        oldest_outstanding: "Oldest Outstanding",
        tasks_exceeded: "Tasks Exceeded",
        avg_days_to_complete: "Average Days to Complete",
      };
      return metricMap[metric] || metric;
    };
  });
  