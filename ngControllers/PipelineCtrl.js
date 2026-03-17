angular.module("sheetApp").controller("PipelineCtrl", [
    "$scope",
    "$http",
    "$localStorage",
    "$timeout",
    "$location",
    "check_auth",
    "myConfig",
    function (
      $scope,
      $http,
      $localStorage,
      $timeout,
      $location,
      check_auth,
      myConfig,
    ) {
      // Initialize variables
      $scope.user_info = {};
      $scope.profile_pic_true = false;
      $scope.profile_pic = "";
      $scope.pipelines = [];
      $scope.metrics = {
        total: 0,
        closed: 0,
        suspended: 0,
        active: 0,
      };
      $scope.newPipeline = {
        title: "",
        description: "",
        lead_id: "",
        source_name: "",
        source_owner: "",
        participants: [],
        discussionDate: new Date(),
        nextDate: null,
        nextTime: null,
        status: "active",
        discussions: [],
      };
      $scope.newDiscussion = {
        title: "",
        date: new Date(),
        content: "",
      };
      $scope.users = [];
      $scope.clients = [];
      $scope.sourceOwners = [];
      $scope.members = [];
      $scope.selectedParticipant = null;
      $scope.charts = {
        statsChart: null,
        analyticsChart: null,
      };
      $scope.minNextDate = new Date();
      $scope.minNextDate.setDate($scope.minNextDate.getDate() + 1);
      $scope.minNextDate = $scope.minNextDate.toISOString().split("T")[0];
      $scope.selectedPipeline = null;
      $scope.currentPipeline = null;
      $scope.isEditMode = false;
      $scope.isEditingExisting = false;
      $scope.filterStatus = "all";
      $scope.isSaving = false; // Added to track saving state
      $scope.isSavingDiscussion = false;
  
      // Load clients
      $scope.loadClients = function () {
        $http
          .get("https://issues.unionsg.com/js/getClients.php")
          .then(function (response) {
            console.log("Clients API response:", response.data);
            $scope.clients = response.data
              .filter(function (client) {
                return client.is_active === "1" || client.is_active === 1;
              })
              .map(function (client) {
                return {
                  id: client.Company_Id,
                  name: client.Company_Name,
                  country: client.Country,
                  Email: client.Email,
                };
              });
  
            $scope.clientsWithEmails = response.data
              .filter(function (client) {
                return (
                  (client.is_active === "1" || client.is_active === 1) &&
                  client.Email
                );
              })
              .map(function (client) {
                return {
                  Company_Id: client.Company_Id,
                  Company_Name: client.Company_Name,
                  Email: client.Email,
                  selected: false,
                };
              });
  
            console.log("Clients loaded from external API:", $scope.clients);
            console.log("Clients with emails:", $scope.clientsWithEmails);
          })
          .catch(function (error) {
            console.error("Error loading clients from external API:", error);
            $scope.error = "Failed to load clients from external API.";
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Failed to load clients from external API.",
            });
          });
      };
  
      // Load client members
      $scope.loadNewClientMembers = function () {
        return $http
          .get("https://issues.unionsg.com/js/getMembersById.php")
          .then(function (response) {
            console.log("Members API response:", response.data);
            $scope.clientMembers = response.data
              .filter(function (member) {
                return member.working === "1" || member.working === 1;
              })
              .map(function (member) {
                return {
                  id: member.Login_Id,
                  name: member.Full_Name,
                  company: member.Company_Id,
                  Email: member.Email,
                };
              });
  
            console.log("Client members loaded:", $scope.clientMembers);
          })
          .catch(function (error) {
            console.error(
              "Error loading client members from external API:",
              error,
            );
            $scope.error = "Failed to load client members from external API.";
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Failed to load client members from external API.",
            });
          });
      };
  
      // Load members for a selected client
      $scope.loadClientMembers = function (clientId) {
        if (!clientId) {
          console.error("No client ID provided.");
          $scope.members = [];
          return;
        }
  
        $scope.members = $scope.clientMembers.filter(function (member) {
          return parseInt(member.company, 10) === parseInt(clientId, 10);
        });
        console.log(
          "Filtered members for client ID",
          clientId,
          ":",
          $scope.members,
        );
  
        // Reset source_owner if it's not valid for the new client
        if ($scope.currentPipeline && $scope.currentPipeline.source_owner) {
          var isValidOwner = $scope.members.some(function (member) {
            return member.name === $scope.currentPipeline.source_owner;
          });
          if (!isValidOwner) {
            $scope.currentPipeline.source_owner = "";
          }
        }
      };
  
      // Load source owners
      $scope.loadSourceOwners = function () {
        $scope.sourceOwners = $scope.users;
        if (!$scope.currentPipeline.source_name) {
          $scope.currentPipeline.source_owner = "";
        }
      };
  
      // Format dates
      $scope.formatDateForDisplay = function (date) {
        if (!date) return "";
        try {
          const dateObj = date instanceof Date ? date : new Date(date);
          if (isNaN(dateObj.getTime())) return "";
          return dateObj.toLocaleDateString("en-US", {
            year: "numeric",
            month: "short",
            day: "numeric",
          });
        } catch (e) {
          return "";
        }
      };
  
      $scope.formatDateForInput = function (date) {
        if (!date) return "";
        try {
          const dateObj = date instanceof Date ? date : new Date(date);
          if (isNaN(dateObj.getTime())) return "";
          return dateObj.toISOString().split("T")[0];
        } catch (e) {
          console.error("Error formatting date for input:", date, e);
          return "";
        }
      };
  
      $scope.formatTimeForInput = function (date) {
        if (!date) return "";
        try {
          const dateObj = date instanceof Date ? date : new Date(date);
          if (isNaN(dateObj.getTime())) return "";
          const hours = String(dateObj.getHours()).padStart(2, "0");
          const minutes = String(dateObj.getMinutes()).padStart(2, "0");
          return `${hours}:${minutes}`;
        } catch (e) {
          console.error("Error formatting time for input:", date, e);
          return "";
        }
      };
  
      // Normalize time
      $scope.normalizeTime = function (model, field) {
        if ($scope[model] && $scope[model][field]) {
          var time = $scope[model][field];
          if (time instanceof Date) {
            $scope[model][field] = $scope.formatTimeForInput(time);
          } else if (typeof time === "string" && !/^\d{2}:\d{2}$/.test(time)) {
            var parsedDate = new Date(`1970-01-01T${time}Z`);
            if (!isNaN(parsedDate.getTime())) {
              $scope[model][field] = $scope.formatTimeForInput(parsedDate);
            } else {
              $scope[model][field] = null;
            }
          }
        }
      };
  
      // Get user name by ID
      $scope.getUserName = function (userId) {
        if (typeof userId === "string" && isNaN(userId)) {
          return userId;
        }
        var user = $scope.users.find(function (u) {
          return u.id == userId;
        });
        return user ? user.name : "Unknown";
      };
  
      // Show custom alert
      $scope.showAlert = function (message) {
        var alertContainer = angular.element(
          document.getElementById("alertContainer"),
        );
        var alertId = "alert-" + new Date().getTime();
        var alertHtml =
          '<div id="' +
          alertId +
          '" class="custom-alert alert-dismissible">' +
          '<i class="bi bi-check-circle-fill alert-icon"></i>' +
          '<span class="alert-message">' +
          message +
          "</span>" +
          '<i class="bi bi-x alert-close" data-bs-dismiss="alert"></i>' +
          "</div>";
        alertContainer.append(alertHtml);
  
        $timeout(function () {
          var alertElement = angular.element(document.getElementById(alertId));
          if (alertElement.length) {
            alertElement.css("animation", "slideOut 0.3s ease-in-out");
            $timeout(function () {
              alertElement.remove();
            }, 300);
          }
        }, 3000);
      };
  
      // Filter participants
      $scope.filterParticipants = function (user) {
        if (!$scope.currentPipeline || !$scope.currentPipeline.participants)
          return true;
        return $scope.currentPipeline.participants.indexOf(user.id) === -1;
      };
  
      // Add participant
      $scope.addParticipant = function () {
        if (
          $scope.selectedParticipant &&
          $scope.currentPipeline &&
          $scope.currentPipeline.participants.indexOf(
            $scope.selectedParticipant,
          ) === -1
        ) {
          $scope.currentPipeline.participants.push($scope.selectedParticipant);
          $scope.selectedParticipant = null;
        }
      };
  
      // Remove participant
      $scope.removeParticipant = function (participant) {
        if (!$scope.currentPipeline) return;
        var index = $scope.currentPipeline.participants.indexOf(participant);
        if (index !== -1) {
          $scope.currentPipeline.participants.splice(index, 1);
        }
      };
  
      // Initialize search variable
      $scope.searchPipelineTitle = "";
  
      // Filter pipelines
      $scope.filterPipelines = function (pipeline) {
        // Filter by search title
        if (
          $scope.searchPipelineTitle &&
          $scope.searchPipelineTitle.trim() !== ""
        ) {
          var searchTerm = $scope.searchPipelineTitle.toLowerCase();
          var pipelineTitle = (pipeline.title || "").toLowerCase();
          if (pipelineTitle.indexOf(searchTerm) === -1) {
            return false;
          }
        }
  
        // Filter by status
        if ($scope.filterStatus === "all") {
          return true;
        } else if ($scope.filterStatus === "myPipelines") {
          if (!$scope.user_info.user_id) {
            console.error("User ID is undefined");
            return false;
          }
          var userId = String($scope.user_info.user_id);
          var leadId = String(pipeline.lead_id);
          var participants = Array.isArray(pipeline.participants)
            ? pipeline.participants.map(String)
            : [];
          var isLead = leadId === userId;
          var isParticipant = participants.indexOf(userId) !== -1;
          return isLead || isParticipant;
        } else {
          return pipeline.status === $scope.filterStatus;
        }
      };
  
      // Log action as discussion
      $scope.logActionAsDiscussion = function (actionTitle, actionContent) {
        if (!$scope.currentPipeline || !$scope.currentPipeline.id) {
          console.error("No pipeline selected for logging action");
          $scope.showAlert("Error: No pipeline selected for logging action.");
          return;
        }
  
        var pipelineId = parseInt($scope.currentPipeline.id, 10);
        if (isNaN(pipelineId) || pipelineId <= 0) {
          console.error("Invalid pipeline ID:", $scope.currentPipeline.id);
          $scope.showAlert("Error: Invalid pipeline ID.");
          return;
        }
  
        var data = {
          pipeline_id: pipelineId,
          title: actionTitle,
          date: $scope.formatDateForInput(new Date()),
          content: actionContent,
          is_system_log: true, // ADD THIS FLAG to prevent email sending
        };
  
        console.log("Logging discussion with data:", data);
  
        $http
          .post("apiSheet/pipeline.php?action=add_discussion", data)
          .then(function (response) {
            console.log("Discussion log response:", response.data);
            if (response.data.success) {
              $scope.loadDiscussions(pipelineId);
            } else {
              console.error(
                "Error logging action as discussion:",
                response.data.message,
              );
              $scope.showAlert("Error logging action: " + response.data.message);
            }
          })
          .catch(function (error) {
            console.error("Error logging action as discussion:", error);
            var msg =
              error.data && error.data.message
                ? error.data.message
                : error.message || "Unknown error";
            $scope.showAlert("Error logging action: " + msg);
          });
      };
  
      // Load user info
      $scope.loadUserInfo = function () {
        try {
          if (
            !$localStorage.user_info ||
            !$localStorage.user_info.data ||
            !$localStorage.user_info.data.user_id
          ) {
            $http
              .get("apiSheet/pipeline.php?action=get_user_info")
              .then(function (response) {
                if (
                  response.data.success &&
                  response.data.data &&
                  response.data.data.user_id
                ) {
                  $localStorage.user_info = { data: response.data.data };
                  $scope.user_info = $localStorage.user_info.data;
                  initializeUserData();
                } else {
                  check_auth.logout();
                  window.location.href = "/login";
                }
              })
              .catch(function (error) {
                console.error("Error fetching user info:", error);
                check_auth.logout();
                window.location.href = "/login";
              });
            return;
          }
          check_auth.verify_auth($localStorage.user_info);
          $scope.user_info = $localStorage.user_info.data || {};
          if (!$scope.user_info.user_id || isNaN($scope.user_info.user_id)) {
            $http
              .get("apiSheet/pipeline.php?action=get_user_info")
              .then(function (response) {
                if (
                  response.data.success &&
                  response.data.data &&
                  response.data.data.user_id
                ) {
                  $localStorage.user_info = { data: response.data.data };
                  $scope.user_info = $localStorage.user_info.data;
                  initializeUserData();
                } else {
                  check_auth.logout();
                  window.location.href = "/login";
                }
              })
              .catch(function (error) {
                console.error("Error fetching user info:", error);
                check_auth.logout();
                window.location.href = "/login";
              });
            return;
          }
          initializeUserData();
        } catch (e) {
          console.error("Error in loadUserInfo:", e);
          check_auth.logout();
          window.location.href = "/login";
        }
      };
  
      // Initialize user-related data
      function initializeUserData() {
        if ($scope.user_info.profile_pic) {
          $scope.profile_pic_true = true;
          $scope.profile_pic = myConfig.file_url + $scope.user_info.profile_pic;
        }
        $scope.loadUsers();
        $scope.loadClients();
        if ($location.path() === "/pipeline_details") {
          var pipelineId = $location.search().id;
          if (pipelineId && !isNaN(parseInt(pipelineId))) {
            $scope.isEditingExisting = true;
            $scope.isEditMode = false;
            $scope.loadPipelineDetails(pipelineId);
          } else {
            $scope.showAlert("Invalid or missing pipeline ID.");
            $scope.isEditingExisting = false;
            $scope.isEditMode = true;
            $scope.currentPipeline = angular.copy($scope.newPipeline);
          }
        } else {
          $scope.loadPipelines();
        }
      }
  
      // Load users
      $scope.loadUsers = function () {
        $http
          .get("apiSheet/pipeline.php?action=get_users")
          .then(function (response) {
            if (response.data.success) {
              $scope.users = response.data.data;
              console.log("user for pipe ", $scope.users);
              $scope.loadSourceOwners();
            } else {
              console.error("Error fetching users:", response.data.message);
            }
          })
          .catch(function (error) {
            console.error("Error fetching users:", error);
          });
      };
  
      // Load pipelines
      $scope.loadPipelines = function () {
        var url = "apiSheet/pipeline.php?action=get_pipelines";
        if ($scope.filterStatus === "myPipelines" && $scope.user_info.user_id) {
          url += "&status=myPipelines&user_id=" + $scope.user_info.user_id;
        } else if ($scope.filterStatus && $scope.filterStatus !== "all") {
          url += "&status=" + $scope.filterStatus;
        }
        $http
          .get(url)
          .then(function (response) {
            if (response.data.success) {
              $scope.pipelines = response.data.data.map(function (pipeline) {
                if (typeof pipeline.participants === "string") {
                  pipeline.participants = pipeline.participants
                    .split(",")
                    .filter((id) => id.trim() !== "")
                    .map(String);
                } else if (!Array.isArray(pipeline.participants)) {
                  pipeline.participants = [];
                }
                pipeline.lead_id = String(pipeline.lead_id);
                pipeline.source_name = pipeline.source_name || "N/A";
                pipeline.source_owner = pipeline.source_owner || "N/A";
                pipeline.status = pipeline.status || "active";
                return pipeline;
              });
              $scope.pipelines.sort(function (a, b) {
                var dateA = new Date(a.created_date_formatted);
                var dateB = new Date(b.created_date_formatted);
                if (dateA.getTime() !== dateB.getTime()) {
                  return dateB - dateA;
                }
                return b.pipeline_id.localeCompare(a.pipeline_id);
              });
              // console.log($scope.pipelines); return false;
              $scope.updateMetrics();
              $scope.initCharts();
            } else {
              console.error("Error fetching pipelines:", response.data.message);
            }
          })
          .catch(function (error) {
            console.error("Error fetching pipelines:", error);
          });
      };
  
      // Load pipeline details
      $scope.loadPipelineDetails = function (pipelineId) {
        console.log(pipelineId, "inside pipe method");
        $http
          .get("apiSheet/pipeline.php?action=get_pipeline&id=" + pipelineId)
          .then(function (response) {
            if (response.data.success) {
              var pipeline = response.data.data;
              console.log("Original pipeline data:", pipeline);
  
              $scope.isEditMode = false;
              $scope.isEditingExisting = true;
  
              $scope.selectedPipeline = {
                id: pipeline.id,
                pipeline_id: pipeline.pipeline_id,
                title: pipeline.title,
                description: pipeline.description || "",
                lead_id: String(pipeline.lead_id),
                source_name: (pipeline.source_name || "").trim(), // TRIM THE VALUE
                source_owner: (pipeline.source_owner || "").trim(), // TRIM THE VALUE
                participants: pipeline.participants.map(String),
                discussionDate: new Date(pipeline.discussion_date_formatted),
                nextDate: pipeline.next_date_formatted
                  ? new Date(pipeline.next_date_formatted)
                  : null,
                nextTime: pipeline.next_time || null,
                status: pipeline.status || "active",
                created_by: pipeline.created_by,
                discussions: [],
              };
  
              $scope.currentPipeline = angular.copy($scope.selectedPipeline);
  
              // Log to verify
              console.log("source_name:", $scope.currentPipeline.source_name);
              console.log("source_owner:", $scope.currentPipeline.source_owner);
  
              if ($scope.currentPipeline.source_name) {
                $scope.loadClientMembers($scope.currentPipeline.source_name);
              }
              $scope.loadDiscussions(pipelineId);
              $scope.loadSourceOwners();
              console.log(
                "Final currentPipeline object:",
                $scope.currentPipeline,
              );
              $scope.showAlert("Pipeline details loaded successfully.");
            } else {
              $scope.showAlert("Error: " + response.data.message);
              $scope.isEditingExisting = false;
              $scope.isEditMode = true;
              $scope.currentPipeline = angular.copy($scope.newPipeline);
            }
          })
          .catch(function (error) {
            console.error("Error fetching pipeline details:", error);
            $scope.showAlert("Error loading pipeline details.");
            $scope.isEditingExisting = false;
            $scope.isEditMode = true;
            $scope.currentPipeline = angular.copy($scope.newPipeline);
          });
      };
  
      // Load discussions
      $scope.loadDiscussions = function (pipelineId) {
        $http
          .get(
            "apiSheet/pipeline.php?action=get_discussions&pipeline_id=" +
              pipelineId,
          )
          .then(function (response) {
            if (response.data.success) {
              $scope.currentPipeline.discussions = response.data.data.map(
                function (discussion) {
                  return {
                    id: discussion.id,
                    title: discussion.title,
                    date: new Date(discussion.date),
                    content: discussion.content,
                    time: discussion.time,
                  };
                },
              );
            } else {
              console.error("Error fetching discussions:", response.data.message);
              $scope.currentPipeline.discussions = [];
            }
          })
          .catch(function (error) {
            console.error("Error fetching discussions:", error);
            $scope.currentPipeline.discussions = [];
            $scope.showAlert("Error loading discussions.");
          });
      };
  
      // Update metrics
      $scope.updateMetrics = function () {
        $scope.metrics.total = $scope.pipelines.length;
        $scope.metrics.active = $scope.pipelines.filter(function (p) {
          return p.status === "active";
        }).length;
        $scope.metrics.closed = $scope.pipelines.filter(function (p) {
          return p.status === "closed";
        }).length;
        $scope.metrics.suspended = $scope.pipelines.filter(function (p) {
          return p.status === "suspended";
        }).length;
      };
  
      // Initialize charts
      $scope.initCharts = function () {
        if ($scope.charts.statsChart) {
          try {
            $scope.charts.statsChart.destroy();
          } catch (e) {}
        }
        if ($scope.charts.analyticsChart) {
          try {
            $scope.charts.analyticsChart.destroy();
          } catch (e) {}
        }
  
        var statsCanvas = document.getElementById("pipelineStatisticsChart");
        if (statsCanvas && statsCanvas.getContext) {
          var ctxStats = statsCanvas.getContext("2d");
          $scope.charts.statsChart = new Chart(ctxStats, {
            type: "doughnut",
            data: {
              labels: ["Active", "Closed", "Suspended"],
              datasets: [
                {
                  data: [
                    $scope.metrics.active,
                    $scope.metrics.closed,
                    $scope.metrics.suspended,
                  ],
                  backgroundColor: ["#11cdef", "#2dce89", "#ffc107"],
                  borderWidth: 2,
                },
              ],
            },
            options: {
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  position: "bottom",
                },
              },
              cutout: "70%",
            },
          });
        }
  
        var months = [
          "Jan",
          "Feb",
          "Mar",
          "Apr",
          "May",
          "Jun",
          "Jul",
          "Aug",
          "Sep",
          "Oct",
          "Nov",
          "Dec",
        ];
        var monthlyCounts = Array(12).fill(0);
        for (var pi = 0; pi < $scope.pipelines.length; pi++) {
          var pipeline = $scope.pipelines[pi];
          if (pipeline.discussion_date_formatted) {
            var dateObj = new Date(pipeline.discussion_date_formatted);
            if (!isNaN(dateObj.getTime())) {
              var monthIndex = dateObj.getMonth();
              monthlyCounts[monthIndex]++;
            }
          }
        }
  
        var currentMonth = new Date().getMonth();
        var displayMonths = months.slice(0, currentMonth + 1);
        var displayCounts = monthlyCounts.slice(0, currentMonth + 1);
  
        var analyticsCanvas = document.getElementById("pipelineAnalyticsChart");
        if (analyticsCanvas && analyticsCanvas.getContext) {
          var ctxAnalytics = analyticsCanvas.getContext("2d");
          $scope.charts.analyticsChart = new Chart(ctxAnalytics, {
            type: "line",
            data: {
              labels: displayMonths,
              datasets: [
                {
                  label: "Total Pipelines",
                  data: displayCounts,
                  borderColor: "#5e72e4",
                  backgroundColor: "rgba(94, 114, 228, 0.2)",
                  tension: 0.4,
                  fill: true,
                  pointRadius: 4,
                  pointBackgroundColor: "#5e72e4",
                },
              ],
            },
            options: {
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  display: false,
                },
              },
              scales: {
                y: {
                  beginAtZero: true,
                  grid: {
                    display: false,
                  },
                  ticks: {
                    display: true,
                  },
                },
                x: {
                  grid: {
                    display: false,
                  },
                  ticks: {
                    display: true,
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 9,
                  },
                },
              },
            },
          });
        }
      };
  
      // Open pipeline modal
      $scope.openAddPipelineModal = function () {
        if (
          $location.path() === "/pipeline_details" &&
          $scope.currentPipeline &&
          $scope.currentPipeline.id
        ) {
          $scope.isEditingExisting = true;
          $scope.isEditMode = false;
          $scope.showAlert("Pipeline details loaded.");
        } else {
          $scope.resetForm();
          $scope.isEditingExisting = false;
          $scope.isEditMode = true;
          $scope.currentPipeline = angular.copy($scope.newPipeline);
          $scope.currentPipeline.nextTime = "09:00";
          $scope.showAlert("Opened page to add a new pipeline.");
        }
        var modalElement = document.getElementById("pipelineModal");
        if (modalElement) {
          var modal = new bootstrap.Modal(modalElement);
          modal.show();
        } else {
          console.error("Pipeline modal element not found.");
          $scope.showAlert("Error: Pipeline modal not found.");
        }
      };
  
      // Enable edit mode
      $scope.enableEditMode = function () {
        $scope.isEditMode = true;
      };
  
      // Navigate back
      $scope.goBack = function () {
        $scope.resetForm();
        $location.path("/pipelines");
      };
  
      // Compare arrays
      $scope.arraysEqual = function (arr1, arr2) {
        if (!Array.isArray(arr1) || !Array.isArray(arr2)) return false;
        if (arr1.length !== arr2.length) return false;
        return arr1.sort().join(",") === arr2.sort().join(",");
      };
  
      // Save pipeline - Updated version
      $scope.savePipeline = function () {
        $scope.isSaving = true;
        console.log("Attempting to save pipeline:", $scope.currentPipeline);
  
        if (!$scope.currentPipeline) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "No pipeline data available.",
          });
          $scope.isSaving = false;
          return;
        }
  
        if (
          $scope.addPipelineForm.$invalid ||
          $scope.currentPipeline.participants.length === 0 ||
          !$scope.currentPipeline.source_name ||
          !$scope.currentPipeline.source_owner
        ) {
          var errors = [];
          if ($scope.addPipelineForm.$invalid) {
            errors.push("Please fill in all required fields.");
          }
          if ($scope.currentPipeline.participants.length === 0) {
            errors.push("At least one participant is required.");
          }
          if (!$scope.currentPipeline.source_name) {
            errors.push("Source is required.");
          }
          if (!$scope.currentPipeline.source_owner) {
            errors.push("Source owner is required.");
          }
  
          Swal.fire({
            icon: "warning",
            title: "Validation Error",
            text: errors.join(" "),
          });
          $scope.isSaving = false;
          return;
        }
  
        var formattedDiscussionDate = $scope.formatDateForInput(
          $scope.currentPipeline.discussionDate,
        );
        if (!formattedDiscussionDate) {
          Swal.fire({
            icon: "error",
            title: "Invalid Date",
            text: "Invalid discussion date format. Expected YYYY-MM-DD.",
          });
          $scope.isSaving = false;
          return;
        }
  
        var formattedNextDate = $scope.formatDateForInput(
          $scope.currentPipeline.nextDate,
        );
        if (!formattedNextDate) {
          Swal.fire({
            icon: "error",
            title: "Invalid Date",
            text: "Invalid next date format. Expected YYYY-MM-DD.",
          });
          $scope.isSaving = false;
          return;
        }
  
        var nextDate = new Date(formattedNextDate);
        if (
          isNaN(nextDate.getTime()) ||
          nextDate <= new Date(formattedDiscussionDate)
        ) {
          Swal.fire({
            icon: "warning",
            title: "Invalid Date Range",
            text:
              "Next date must be after the discussion date (" +
              formattedDiscussionDate +
              ").",
          });
          $scope.isSaving = false;
          return;
        }
  
        var minNextDate = new Date($scope.minNextDate);
        if (nextDate < minNextDate) {
          Swal.fire({
            icon: "warning",
            title: "Invalid Date",
            text: "Next date must be on or after " + $scope.minNextDate + ".",
          });
          $scope.isSaving = false;
          return;
        }
  
        $scope.normalizeTime("currentPipeline", "nextTime");
        var nextDiscussionTime = $scope.currentPipeline.nextTime || null;
        if (nextDiscussionTime && !/^\d{2}:\d{2}$/.test(nextDiscussionTime)) {
          Swal.fire({
            icon: "error",
            title: "Invalid Time",
            text: "Invalid next discussion time format. Expected HH:mm.",
          });
          $scope.isSaving = false;
          return;
        }
  
        var changes = [];
        if ($scope.isEditingExisting && $scope.selectedPipeline) {
          if ($scope.currentPipeline.title !== $scope.selectedPipeline.title) {
            changes.push(
              `Title changed from "${$scope.selectedPipeline.title}" to "${$scope.currentPipeline.title}"`,
            );
          }
          if (
            $scope.currentPipeline.description !==
            $scope.selectedPipeline.description
          ) {
            changes.push(
              `Description changed from "${$scope.selectedPipeline.description || "None"}" to "${$scope.currentPipeline.description || "None"}"`,
            );
          }
          if (
            $scope.currentPipeline.lead_id !== $scope.selectedPipeline.lead_id
          ) {
            changes.push(
              `Lead changed from "${$scope.getUserName($scope.selectedPipeline.lead_id)}" to "${$scope.getUserName($scope.currentPipeline.lead_id)}"`,
            );
          }
          if (
            $scope.currentPipeline.source_name !==
            $scope.selectedPipeline.source_name
          ) {
            changes.push(
              `Source changed from "${$scope.selectedPipeline.source_name || "N/A"}" to "${$scope.currentPipeline.source_name || "N/A"}"`,
            );
          }
          if (
            $scope.currentPipeline.source_owner !==
            $scope.selectedPipeline.source_owner
          ) {
            changes.push(
              `Source Owner changed from "${$scope.selectedPipeline.source_owner || "N/A"}" to "${$scope.currentPipeline.source_owner || "N/A"}"`,
            );
          }
          if (
            !$scope.arraysEqual(
              $scope.currentPipeline.participants,
              $scope.selectedPipeline.participants,
            )
          ) {
            var oldParticipants = $scope.selectedPipeline.participants
              .map(function (id) {
                return $scope.getUserName(id);
              })
              .join(", ");
            var newParticipants = $scope.currentPipeline.participants
              .map(function (id) {
                return $scope.getUserName(id);
              })
              .join(", ");
            changes.push(
              `Participants changed from "${oldParticipants || "None"}" to "${newParticipants || "None"}"`,
            );
          }
          if (
            $scope.formatDateForInput($scope.currentPipeline.discussionDate) !==
            $scope.formatDateForInput($scope.selectedPipeline.discussionDate)
          ) {
            changes.push(
              `Discussion Date changed from "${$scope.formatDateForDisplay($scope.selectedPipeline.discussionDate)}" to "${$scope.formatDateForDisplay($scope.currentPipeline.discussionDate)}"`,
            );
          }
          if (
            $scope.formatDateForInput($scope.currentPipeline.nextDate) !==
            $scope.formatDateForInput($scope.selectedPipeline.nextDate)
          ) {
            changes.push(
              `Next Discussion Date changed from "${$scope.formatDateForDisplay($scope.selectedPipeline.nextDate)}" to "${$scope.formatDateForDisplay($scope.currentPipeline.nextDate)}"`,
            );
          }
          if (
            $scope.currentPipeline.nextTime !== $scope.selectedPipeline.nextTime
          ) {
            changes.push(
              `Next Discussion Time changed from "${$scope.selectedPipeline.nextTime || "N/A"}" to "${$scope.currentPipeline.nextTime || "N/A"}"`,
            );
          }
        }
  
        var data = {
          title: $scope.currentPipeline.title || "",
          description: $scope.currentPipeline.description || "",
          lead_id: parseInt($scope.currentPipeline.lead_id, 10) || 0,
          source_name: $scope.currentPipeline.source_name || "",
          source_owner: $scope.currentPipeline.source_owner || "",
          participants: $scope.currentPipeline.participants.map(function (id) {
            return parseInt(id, 10);
          }),
          discussion_date: formattedDiscussionDate,
          next_date: formattedNextDate,
          next_time: nextDiscussionTime,
        };
  
        if ($scope.isEditingExisting && $scope.currentPipeline.id) {
          data.id = parseInt($scope.currentPipeline.id, 10);
          data.changes = changes;
        }
  
        if (!$scope.isEditingExisting) {
          if (!$scope.user_info.user_id) {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "User ID is not available.",
            });
            $scope.isSaving = false;
            return;
          }
          data.created_by = parseInt($scope.user_info.user_id, 10);
        }
  
        var url = $scope.isEditingExisting
          ? "apiSheet/pipeline.php?action=update_pipeline"
          : "apiSheet/pipeline.php?action=create_pipeline";
        var isEditingExisting = $scope.isEditingExisting;
        var pipelineId = isEditingExisting ? $scope.currentPipeline.id : null;
  
        console.log("Sending pipeline data to backend:", data);
  
        $http
          .post(url, data)
          .then(function (response) {
            console.log("Pipeline save response:", response.data);
            if (response.data && response.data.success) {
              if (isEditingExisting && changes.length > 0) {
                $scope.logActionAsDiscussion(
                  "Pipeline Updated",
                  `Pipeline details updated:\n- ${changes.join("\n- ")}`,
                );
              }
  
              // Close the Bootstrap modal before showing SweetAlert2
              var modalElement = document.getElementById("pipelineModal");
              var modal = modalElement
                ? bootstrap.Modal.getInstance(modalElement) ||
                  new bootstrap.Modal(modalElement)
                : null;
  
              if (modal) {
                try {
                  modal.hide();
                } catch (e) {
                  console.error("Error hiding pipeline modal:", e);
                }
              }
  
              // Remove any lingering modal backdrops
              $timeout(function () {
                var backdrops = document.querySelectorAll(".modal-backdrop");
                backdrops.forEach(function (backdrop) {
                  backdrop.remove();
                });
                document.body.classList.remove("modal-open");
                document.body.style.removeProperty("padding-right");
  
                if (isEditingExisting) {
                  // Stay on the same page and reload details
                  $scope.loadPipelineDetails(pipelineId);
                  $scope.isEditMode = false;
  
                  Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Pipeline updated successfully.",
                    confirmButtonColor: "#5e72e4",
                  });
                } else {
                  // For new pipeline creation
                  var newPipelineId =
                    response.data.data && response.data.data.id
                      ? response.data.data.id
                      : "Unknown";
  
                  Swal.fire({
                    icon: "success",
                    title: "Pipeline Created!",
                    text:
                      response.data.message || "Pipeline created successfully.",
                    confirmButtonColor: "#5e72e4",
                  }).then(function () {
                    $scope.loadPipelines();
                    $scope.resetForm();
                    $location.path("/pipelines");
                    $scope.$apply();
                  });
                }
              }, 100); // Small delay to ensure modal closes properly
            } else {
              Swal.fire({
                icon: "error",
                title: "Error",
                text:
                  response.data && response.data.message
                    ? response.data.message
                    : "Unknown server response",
              });
            }
          })
          .catch(function (error) {
            var msg = "Unknown error";
            if (error && error.data && error.data.message) {
              msg = error.data.message;
            } else if (error && error.statusText) {
              msg = error.statusText;
            } else if (error && error.message) {
              msg = error.message;
            }
            console.error("Error saving pipeline:", error);
  
            Swal.fire({
              icon: "error",
              title: "Save Failed",
              text: "Error saving pipeline: " + msg,
            });
          })
          .finally(function () {
            $scope.isSaving = false;
          });
      };
  
      // Close pipeline - Updated version
      $scope.closePipeline = function () {
        if ($scope.currentPipeline) {
          Swal.fire({
            title: "Close Pipeline?",
            text: "Are you sure you want to close this pipeline?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#5e72e4",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, close it!",
            cancelButtonText: "Cancel",
          }).then(function (result) {
            if (result.isConfirmed) {
              $http
                .post("apiSheet/pipeline.php?action=close_pipeline", {
                  id: $scope.currentPipeline.id,
                })
                .then(function (response) {
                  if (response.data.success) {
                    $scope.logActionAsDiscussion(
                      "Pipeline Closed",
                      `Pipeline "${$scope.currentPipeline.title}" closed by ${$scope.getUserName($scope.user_info.user_id)}.`,
                    );
  
                    // console.log('add_project?pipeline_id='+$scope.currentPipeline.id, "the id this");
                    // return false;
                    // Reload the current pipeline details instead of navigating away
                    $scope.loadPipelineDetails($scope.currentPipeline.id);
                    console.log(
                      "/add_project?pipeline_id=" + $scope.currentPipeline.id,
                    );
                    // return false;
  
                    // Swal.fire({
                    //     icon: "success",
                    //     title: "Closed!",
                    //     text: "Pipeline has been closed. Redirecting to the create project page in 5 seconds...",
                    //     confirmButtonColor: "#5e72e4"
                    // });
  
                    // // Swal.fire([
                    // //     {
                    // //       title: "Pipeline has been closed. Redirecting to the create project page ...  ",
                    // //       // showLoaderOnConfirm: true,
                    // //       onBeforeOpen: () => {
                    // //         Swal.showLoading();
                    // //       },
                    // //       showLoaderOnConfirm: true,
                    // //     },
                    // //   ]);
  
                    // setTimeout(function() {
                    //     window.location.href='add_project?pipeline_id='+$scope.currentPipeline.id;
                    // }, 5000);
  
                    let timerInterval;
                    let timeLeft = 5;
  
                    Swal.fire({
                      icon: "success",
                      title: "Closed!",
                      html: `Pipeline has been closed.<br>
                                              Redirecting to the create project page in 
                                              <b id="countdown">5</b> seconds...`,
                      confirmButtonColor: "#5e72e4",
                      showConfirmButton: false,
                      timer: 5000,
                      timerProgressBar: true,
                      didOpen: () => {
                        const countdownEl =
                          Swal.getHtmlContainer().querySelector("#countdown");
  
                        timerInterval = setInterval(() => {
                          timeLeft--;
                          if (countdownEl) {
                            countdownEl.textContent = timeLeft;
                          }
                        }, 1000);
                      },
                      willClose: () => {
                        clearInterval(timerInterval);
                      },
                    });
  
                    setTimeout(function () {
                      window.location.href =
                        "add_project?pipeline_id=" + $scope.currentPipeline.id;
                    }, 5000);
  
                    // $location.path('/add_project?pipeline_id'+$scope.currentPipeline.id);
  
                    // $timeout(()=>{
                    //     window.location.href = 'add_project?pipeline_id='+$scope.currentPipeline.id
                    // },1000);
                  } else {
                    Swal.fire({
                      icon: "error",
                      title: "Error",
                      text: response.data.message || "Failed to close pipeline.",
                    });
                  }
                })
                .catch(function (error) {
                  console.error("Error closing pipeline:", error);
                  Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:
                      error.data && error.data.message
                        ? error.data.message
                        : error.message || "Unknown error",
                  });
                });
            }
          });
        }
      };
  
      // Suspend pipeline - Updated version
      $scope.suspendPipeline = function () {
        if ($scope.currentPipeline) {
          Swal.fire({
            title: "Suspend Pipeline?",
            text: "Are you sure you want to suspend this pipeline?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#5e72e4",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, suspend it!",
            cancelButtonText: "Cancel",
          }).then(function (result) {
            if (result.isConfirmed) {
              $http
                .post("apiSheet/pipeline.php?action=suspend_pipeline", {
                  id: $scope.currentPipeline.id,
                })
                .then(function (response) {
                  if (response.data.success) {
                    $scope.logActionAsDiscussion(
                      "Pipeline Suspended",
                      `Pipeline "${$scope.currentPipeline.title}" suspended by ${$scope.getUserName($scope.user_info.user_id)}.`,
                    );
  
                    // Reload the current pipeline details instead of navigating away
                    $scope.loadPipelineDetails($scope.currentPipeline.id);
  
                    Swal.fire({
                      icon: "success",
                      title: "Suspended!",
                      text: "Pipeline has been suspended successfully.",
                      confirmButtonColor: "#5e72e4",
                    });
                  } else {
                    Swal.fire({
                      icon: "error",
                      title: "Error",
                      text:
                        response.data.message || "Failed to suspend pipeline.",
                    });
                  }
                })
                .catch(function (error) {
                  console.error("Error suspending pipeline:", error);
                  Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:
                      error.data && error.data.message
                        ? error.data.message
                        : error.message || "Unknown error",
                  });
                });
            }
          });
        }
      };
  
      // Reset form
      $scope.resetForm = function () {
        $scope.newPipeline = {
          title: "",
          description: "",
          lead_id: "",
          source_name: "",
          source_owner: "",
          participants: [],
          discussionDate: new Date(),
          nextDate: null,
          nextTime: null,
          status: "active",
          discussions: [],
        };
        $scope.newDiscussion = {
          title: "",
          date: new Date(),
          content: "",
        };
        $scope.selectedPipeline = null;
        $scope.currentPipeline = null;
        $scope.selectedParticipant = null;
        $scope.members = [];
        $scope.isEditMode = false;
        $scope.isEditingExisting = false;
        $scope.isSaving = false; // Reset saving state
        if ($scope.addPipelineForm) {
          try {
            $scope.addPipelineForm.$setPristine();
            $scope.addPipelineForm.$setUntouched();
          } catch (e) {}
        }
        if ($scope.discussionForm) {
          try {
            $scope.discussionForm.$setPristine();
            $scope.discussionForm.$setUntouched();
          } catch (e) {}
        }
      };
  
      // Reset discussion form
      $scope.resetDiscussionForm = function () {
        $scope.newDiscussion = {
          title: "",
          date: new Date(),
          time: "",
          content: "",
        };
        $scope.isSavingDiscussion = false; // Reset saving state
        if ($scope.discussionForm) {
          try {
            $scope.discussionForm.$setPristine();
            $scope.discussionForm.$setUntouched();
          } catch (e) {}
        }
      };
  
      // Open discussion modal
      $scope.addDiscussion = function () {
        $scope.resetDiscussionForm();
        var modalElement = document.getElementById("discussionModal");
        if (modalElement) {
          var modal = new bootstrap.Modal(modalElement);
          modal.show();
        } else {
          console.error("Discussion modal element not found.");
          $scope.showAlert("Error: Discussion modal not found.");
        }
      };
  
      // Save discussion - Updated to include saving state with time handling
      $scope.saveDiscussion = function () {
        if (
          $scope.discussionForm.$invalid ||
          !$scope.currentPipeline ||
          !$scope.currentPipeline.id
        ) {
          Swal.fire({
            icon: "warning",
            title: "Validation Error",
            text: "Please fill in all required fields and ensure a pipeline is selected.",
          });
          return;
        }
  
        // Set saving state
        $scope.isSavingDiscussion = true;
  
        // Combine date and time into datetime format
        var discussionDate = new Date($scope.newDiscussion.date);
  
        // console.log(discussionTime); return false;
  
        // Safely handle time
        if (
          $scope.newDiscussion.time &&
          typeof $scope.newDiscussion.time === "string"
        ) {
          try {
            var timeParts = $scope.newDiscussion.time.split(":");
            if (timeParts.length >= 2) {
              discussionDate.setHours(parseInt(timeParts[0]) || 0);
              discussionDate.setMinutes(parseInt(timeParts[1]) || 0);
              discussionDate.setSeconds(0);
            }
  
            console.log(discussionDate, "the discussion date!!!");
            return false;
          } catch (e) {
            console.warn("Error parsing time, using current time:", e);
            var now = new Date();
            discussionDate.setHours(now.getHours());
            discussionDate.setMinutes(now.getMinutes());
            discussionDate.setSeconds(0);
          }
        } else {
          // Default to current time if no time specified
          var now = new Date();
          discussionDate.setHours(now.getHours());
          discussionDate.setMinutes(now.getMinutes());
          discussionDate.setSeconds(0);
        }
  
        // Format as YYYY-MM-DD HH:MM:SS for backend
        var formattedDateTime = discussionDate
          .toISOString()
          .slice(0, 19)
          .replace("T", " ");
        var discussionTime = new Date($scope.newDiscussion.time)
          .toISOString()
          .split("T")[1]
          .slice(0, 5);
  
        var data = {
          pipeline_id: parseInt($scope.currentPipeline.id, 10),
          title: $scope.newDiscussion.title || "Discussion",
          date: formattedDateTime, // Send datetime with time
          time: discussionTime,
          content: $scope.newDiscussion.content,
        };
  
        console.log("Saving discussion with datetime data:", data);
  
        var modalElement = document.getElementById("discussionModal");
        var modal = modalElement
          ? bootstrap.Modal.getInstance(modalElement) ||
            new bootstrap.Modal(modalElement)
          : null;
  
        $http
          .post("apiSheet/pipeline.php?action=add_discussion", data)
          .then(function (response) {
            console.log("Discussion save response:", response.data);
            if (response.data.success) {
              $scope.resetDiscussionForm();
  
              if (modal) {
                try {
                  modal.hide();
                } catch (e) {
                  console.error("Error hiding discussion modal:", e);
                }
              }
  
              $timeout(function () {
                var backdrops = document.querySelectorAll(".modal-backdrop");
                backdrops.forEach(function (backdrop) {
                  backdrop.remove();
                });
                document.body.classList.remove("modal-open");
                document.body.style.removeProperty("padding-right");
  
                $scope.loadDiscussions(data.pipeline_id);
  
                Swal.fire({
                  icon: "success",
                  title: "Success!",
                  text: "Discussion saved successfully.",
                  confirmButtonColor: "#5e72e4",
                });
              }, 100);
            } else {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: response.data.message || "Failed to save discussion.",
              });
            }
          })
          .catch(function (error) {
            console.error("Error saving discussion:", error);
            var msg =
              error.data && error.data.message
                ? error.data.message
                : error.message || "Unknown error";
  
            Swal.fire({
              icon: "error",
              title: "Save Failed",
              text: "Error saving discussion: " + msg,
            });
          })
          .finally(function () {
            $scope.isSavingDiscussion = false;
          });
      };
  
      // Export to PDF
      $scope.exportToPDF = function () {
        var jsPDF =
          window.jspdf && window.jspdf.jsPDF
            ? window.jspdf.jsPDF
            : (window.jspdf || {}).jsPDF;
        if (!jsPDF && !window.jsPDF && !window.jspdf) {
          $scope.showAlert("PDF library not loaded.");
          return;
        }
        var doc =
          window.jspdf && window.jspdf.jsPDF
            ? new window.jspdf.jsPDF({
                orientation: "portrait",
                unit: "mm",
                format: "a4",
              })
            : new window.jsPDF({
                orientation: "portrait",
                unit: "mm",
                format: "a4",
              });
        var pageWidth = 210;
        var pageHeight = 297;
        var margin = 10;
        var y = margin;
  
        function checkPageBreak(additionalHeight) {
          if (y + additionalHeight > pageHeight - margin) {
            doc.addPage();
            y = margin + 20;
            return true;
          }
          return false;
        }
  
        function addHeader() {
          doc.setFontSize(20);
          doc.setFont("helvetica", "bold");
          doc.setTextColor(40, 40, 40);
          doc.text("Pipeline Report", pageWidth / 2, 15, { align: "center" });
          doc.setFontSize(10);
          doc.setFont("helvetica", "italic");
          doc.setTextColor(100);
          doc.text(
            "Generated on: " + new Date().toLocaleString(),
            pageWidth - margin,
            15,
            { align: "right" },
          );
          y = 30;
        }
  
        function addSectionTitle(title) {
          checkPageBreak(12);
          doc.setFillColor(94, 114, 228);
          doc.rect(margin, y, pageWidth - 2 * margin, 8, "F");
          doc.setFontSize(12);
          doc.setFont("helvetica", "bold");
          doc.setTextColor(255, 255, 255);
          doc.text(title, margin + 2, y + 6);
          y += 14;
        }
  
        addHeader();
        addSectionTitle("Pipeline Overview");
        var headers = [
          "Pipeline ID",
          "Pipeline Title",
          "Date",
          "Lead",
          "Source",
          "Next Date",
        ];
        var colWidths = [30, 50, 30, 30, 30, 30];
        var rowHeight = 10;
        var cellPadding = 2;
        doc.setFontSize(10);
        doc.setFont("helvetica", "bold");
        doc.setTextColor(255, 255, 255);
        doc.setFillColor(94, 114, 228);
        checkPageBreak(rowHeight);
        for (var h = 0; h < headers.length; h++) {
          var header = headers[h];
          var left =
            margin +
            colWidths.slice(0, h).reduce(function (a, b) {
              return a + b;
            }, 0);
          doc.roundedRect(left, y, colWidths[h], rowHeight, 2, 2, "F");
          doc.text(header, left + cellPadding, y + rowHeight - cellPadding);
        }
        y += rowHeight;
        doc.setFont("helvetica", "normal");
        doc.setTextColor(0, 0, 0);
        for (var r = 0; r < $scope.pipelines.length; r++) {
          var row = $scope.pipelines[r];
          if (
            $scope.filterStatus === "all" ||
            row.status === $scope.filterStatus
          ) {
            checkPageBreak(rowHeight);
            var rowData = [
              row.pipeline_id,
              row.title,
              row.discussion_date_formatted,
              row.lead,
              row.source_name || "N/A",
              row.next_date_formatted,
            ];
            var isEven = r % 2 === 0;
            for (var c = 0; c < rowData.length; c++) {
              var cell = rowData[c] || "N/A";
              var splitText = doc.splitTextToSize(
                cell,
                colWidths[c] - 2 * cellPadding,
              );
              if (isEven) {
                doc.setFillColor(255, 245, 255);
              } else {
                doc.setFillColor(245, 245, 245);
              }
              var leftPos =
                margin +
                colWidths.slice(0, c).reduce(function (a, b) {
                  return a + b;
                }, 0);
              doc.roundedRect(leftPos, y, colWidths[c], rowHeight, 2, 2, "F");
              doc.text(
                splitText,
                leftPos + cellPadding,
                y + rowHeight - cellPadding,
              );
            }
            y += rowHeight;
          }
        }
        doc.save("Pipeline_Report.pdf");
        $scope.showAlert("Pipeline report exported to PDF successfully.");
      };
  
      // Get client members
      $scope.getClientMembers = function (users, clientId) {
        console.log("Filtering members for client ID:", clientId);
        console.log("Available users:", users);
        return users.filter(function (user) {
          return parseInt(user.company, 10) === parseInt(clientId, 10);
        });
      };
  
      // Handle source name change
      $scope.$watch("currentPipeline.source_name", function (newValue, oldValue) {
        if (newValue !== oldValue && newValue) {
          console.log("Source name changed to:", newValue);
          $scope.loadClientMembers(newValue);
        } else if (!newValue) {
          $scope.members = [];
          $scope.currentPipeline.source_owner = "";
        }
      });
  
      // Logout
      $scope.logout = function () {
        check_auth.logout($scope.user_info.user_id);
      };
  
      $scope.init = function () {
        $scope.loadUserInfo();
        $scope.loadUsers();
        $scope.loadNewClientMembers().then(function () {
          if ($location.path() === "/pipeline_details") {
            var pipelineId = $location.search().id;
            console.log("pipelin id ", pipelineId);
            if (pipelineId && !isNaN(parseInt(pipelineId))) {
              $scope.isEditingExisting = true;
              $scope.isEditMode = false;
              $scope.loadPipelineDetails(pipelineId);
            } else {
              $scope.showAlert("Invalid or missing pipeline ID.");
              $scope.isEditingExisting = false;
              $scope.isEditMode = true;
              $scope.currentPipeline = angular.copy($scope.newPipeline);
            }
          } else {
            $scope.loadPipelines();
          }
        });
      };
  
      $scope.init();
    },
  ]);
  