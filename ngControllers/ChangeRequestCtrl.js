angular.module('sheetApp').controller('ChangeRequestCtrl', ['$scope', '$http', '$timeout', '$location', '$localStorage', 'check_auth', 'myConfig', function($scope, $http, $timeout, $location, $localStorage, check_auth, myConfig) {
    // Initialize variables
    $scope.user_info = {};
    $scope.isApprover = false;
    $scope.profile_pic_true = false;
    $scope.profile_pic = '';
    $scope.showSuccessModal = false;
    $scope.isSubmitting = false;
    $scope.referenceData = {};
    $scope.clients = [];
    $scope.users = [];
    $scope.countries = [];
    $scope.projects = [];
    $scope.filteredProjects = [];
    $scope.showProjectSuggestions = false;
    $scope.requests = [];
    $scope.filteredRequests = [];
    $scope.currentFilter = 'all';
    $scope.filterDisplayText = 'All';
    $scope.selectedRequest = {};
    $scope.isImplementationSaved = false;
    $scope.allApprovalsComplete = false;
    $scope.approval = {
        deptHead: { name: '', date: null, dateFormatted: '', comments: '', approved: false },
        qa: { name: '', date: null, dateFormatted: '', comments: '', approved: false },
        ceo: { name: '', date: null, dateFormatted: '', comments: '', approved: false }
    };
    $scope.newRequest = {
        submitted_by: null,
        requestBy: null,
        implementer: null,
        projectId: null,
        projectIdFormatted: ''
    };

    // Helper function to format date to YYYY-MM-DD
    $scope.formatDateForInput = function(date) {
        if (!date) return '';
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    // Load user info
    $scope.loadUserInfo = function() {
        try {
            console.log('localStorage.user_info:', $localStorage.user_info);
            if (!$localStorage.user_info || !$localStorage.user_info.data || !$localStorage.user_info.data.user_id) {
                console.error('Invalid or missing user info in localStorage, attempting API fetch');
                $http.get('apiSheet/change_request.php?action=get_user_info')
                    .then(function(response) {
                        console.log('get_user_info response:', response.data);
                        if (response.data.success && response.data.data && response.data.data.user_id) {
                            $localStorage.user_info = { data: response.data.data };
                            $scope.user_info = $localStorage.user_info.data;
                            console.log('API user info loaded:', $scope.user_info);
                            initializeUserData();
                        } else {
                            console.error('Failed to load user info from API:', response.data.message || 'No message');
                            check_auth.logout();
                            window.location.href = '/login';
                        }
                    })
                    .catch(function(error) {
                        console.error('Error fetching user info from API:', error);
                        check_auth.logout();
                        window.location.href = '/login';
                    });
                return;
            }

            check_auth.verify_auth($localStorage.user_info);
            $scope.user_info = $localStorage.user_info.data || {};
            console.log('User info from localStorage:', $scope.user_info);

            if (!$scope.user_info.user_id || isNaN($scope.user_info.user_id)) {
                console.error('User ID is missing or invalid in localStorage, attempting API fetch');
                $http.get('apiSheet/change_request.php?action=get_user_info')
                    .then(function(response) {
                        console.log('get_user_info response:', response.data);
                        if (response.data.success && response.data.data && response.data.data.user_id) {
                            $localStorage.user_info = { data: response.data.data };
                            $scope.user_info = $localStorage.user_info.data;
                            console.log('API user info loaded:', $scope.user_info);
                            initializeUserData();
                        } else {
                            console.error('Failed to load user info from API:', response.data.message || 'No message');
                            check_auth.logout();
                            window.location.href = '/login';
                        }
                    })
                    .catch(function(error) {
                        console.error('Error fetching user info from API:', error);
                        check_auth.logout();
                        window.location.href = '/login';
                    });
                return;
            }

            initializeUserData();
        } catch (e) {
            console.error('Error in loadUserInfo:', e);
            check_auth.logout();
            window.location.href = '/login';
        }
    };

    // Helper function to initialize user-related data
    function initializeUserData() {
        $scope.isApprover = $scope.user_info.can_approve == 1;
        console.log('isApprover set to:', $scope.isApprover);

        if ($scope.user_info.profile_pic) {
            $scope.profile_pic_true = true;
            $scope.profile_pic = myConfig.file_url + $scope.user_info.profile_pic;
        }

        $scope.newRequest.submitted_by = $scope.user_info.user_id;
        $scope.newRequest.requestBy = $scope.user_info.user_id;
        $scope.newRequest.implementer = $scope.user_info.user_id;
        console.log('newRequest initialized:', $scope.newRequest);
    }

    // Check if the logged-in user is eligible to approve at a specific level
    $scope.isUserEligible = function(approvalLevel) {
        if (!$scope.user_info || !$scope.user_info.user_id || !$scope.selectedRequest || !$scope.selectedRequest.approval_status) {
            return false;
        }

        // Check if user has already approved at another level
        var hasApprovedOtherLevel = false;
        var currentUserId = $scope.user_info.user_id;
        var approvalStatus = $scope.selectedRequest.approval_status;

        if (approvalLevel !== 'dept_head' && approvalStatus.dept_head.comments && approvalStatus.dept_head.name === ($scope.user_info.first_name + ' ' + $scope.user_info.last_name)) {
            hasApprovedOtherLevel = true;
        }
        if (approvalLevel !== 'qa' && approvalStatus.qa.comments && approvalStatus.qa.name === ($scope.user_info.first_name + ' ' + $scope.user_info.last_name)) {
            hasApprovedOtherLevel = true;
        }
        if (approvalLevel !== 'ceo' && approvalStatus.ceo.comments && approvalStatus.ceo.name === ($scope.user_info.first_name + ' ' + $scope.user_info.last_name)) {
            hasApprovedOtherLevel = true;
        }

        if (hasApprovedOtherLevel) {
            return false;
        }

        // Existing eligibility checks
        if (approvalLevel === 'dept_head') {
            return $scope.user_info.is_dept_head == 1 && $scope.user_info.can_approve == 1;
        }
        if (!$scope.referenceData.approvers || !$scope.referenceData.approvers[approvalLevel]) {
            return false;
        }
        return $scope.referenceData.approvers[approvalLevel].some(function(person) {
            return person.id == $scope.user_info.user_id;
        });
    };

    // Set default approver names and current date based on logged-in user
    $scope.setDefaultApproverNames = function() {
        if ($scope.user_info.user_id) {
            var userFullName = $scope.user_info.first_name + ' ' + $scope.user_info.last_name;
            var currentDate = new Date();
            var formattedCurrentDate = $scope.formatDateForInput(currentDate);

            if ($scope.isUserEligible('dept_head') && !$scope.approval.deptHead.comments) {
                $scope.approval.deptHead.name = userFullName;
                $scope.approval.deptHead.date = currentDate;
                $scope.approval.deptHead.dateFormatted = formattedCurrentDate;
            }

            if ($scope.isUserEligible('qa') && !$scope.approval.qa.comments) {
                $scope.approval.qa.name = userFullName;
                $scope.approval.qa.date = currentDate;
                $scope.approval.qa.dateFormatted = formattedCurrentDate;
            }

            if ($scope.isUserEligible('ceo') && !$scope.approval.ceo.comments) {
                $scope.approval.ceo.name = userFullName;
                $scope.approval.ceo.date = currentDate;
                $scope.approval.ceo.dateFormatted = formattedCurrentDate;
            }
        }
    };

    // Check if all approval stages are complete
    $scope.checkAllApprovalsComplete = function() {
        $scope.allApprovalsComplete = $scope.approval.deptHead.comments &&
            $scope.approval.qa.comments &&
            $scope.approval.ceo.comments;
    };

    // Load users for dropdown
    $scope.loadUsers = function() {
        $http.get('apiSheet/change_request.php?action=get_users')
            .then(function(response) {
                if (response.data.success) {
                    $scope.users = response.data.data.map(function(user) {
                        return {
                            id: String(user.user_id || user.id),
                            name: user.name || `${user.first_name} ${user.last_name}`
                        };
                    });
                    console.log('Users loaded:', $scope.users);
                    if ($scope.user_info.user_id && !$scope.newRequest.requestBy) {
                        $scope.newRequest.requestBy = String($scope.user_info.user_id);
                        $scope.newRequest.implementer = String($scope.user_info.user_id);
                        console.log('requestBy and implementer set after users loaded:', $scope.newRequest.requestBy, $scope.newRequest.implementer);
                    }
                    const userExists = $scope.users.some(function(user) {
                        return user.id === $scope.user_info.user_id;
                    });
                    console.log('Logged-in user exists in users list:', userExists);
                    $timeout(function() {
                        $scope.$apply();
                    });
                } else {
                    console.error('Failed to load users:', response.data.message);
                    if ($scope.referenceData.users) {
                        $scope.users = $scope.referenceData.users.map(function(user) {
                            return {
                                id: String(user.user_id || user.id),
                                name: user.name || `${user.first_name} ${user.last_name}`
                            };
                        });
                    }
                }
            })
            .catch(function(error) {
                console.error('Error loading users:', error);
                if ($scope.referenceData.users) {
                    $scope.users = $scope.referenceData.users.map(function(user) {
                        return {
                            id: String(user.user_id || user.id),
                            name: user.name || `${user.first_name} ${user.last_name}`
                        };
                    });
                }
            });
    };

    // Logout function
    $scope.logout = function() {
        check_auth.logout($scope.user_info.user_id);
    };

    // Format time from HH:MM AM/PM to database format (HH:MM:SS)
    $scope.formatTimeForDatabase = function(hours, minutes, period) {
        if (!hours || !minutes || !period) return null;
        hours = parseInt(hours);
        minutes = parseInt(minutes);
        if (period === 'PM' && hours < 12) hours += 12;
        if (period === 'AM' && hours === 12) hours = 0;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:00`;
    };

    // Parse database time (HH:MM:SS) to display format
    $scope.parseDatabaseTime = function(time) {
        if (!time) return { hours: null, minutes: null, period: 'AM' };
        const [hours, minutes] = time.split(':').map(Number);
        const period = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 === 0 ? 12 : hours % 12;
        return {
            hours: displayHours,
            minutes: minutes,
            period: period
        };
    };

    // Load reference data for dropdowns
    $scope.loadReferenceData = function() {
        $http.get('apiSheet/change_request.php?action=get_reference_data')
            .then(function(response) {
                if (response.data.success) {
                    $scope.referenceData = response.data.data;
                    console.log('Reference data loaded:', $scope.referenceData);
                    $scope.setDefaultApproverNames();
                    $scope.checkAllApprovalsComplete();
                } else {
                    console.error('Failed to load reference data:', response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error loading reference data:', error);
            });
    };

    // Update loadClients to fetch from the new API
    $scope.loadClients = function() {
        $http.get('https://issues.unionsg.com/js/getClients.php')
            .then(function(response) {
                console.log('Clients API response:', response.data);
                $scope.clients = response.data
                    .filter(function(client) {
                        return client.is_active === "1" || client.is_active === 1;
                    })
                    .map(function(client) {
                        return {
                            id: client.Company_Id,
                            name: client.Company_Name,
                            country: client.Country
                        };
                    });
                console.log('Clients loaded from external API:', $scope.clients);
            })
            .catch(function(error) {
                console.error('Error loading clients from external API:', error);
                $http.get('apiSheet/change_request.php?action=get_clients')
                    .then(function(fallbackResponse) {
                        if (fallbackResponse.data.success) {
                            $scope.clients = fallbackResponse.data.data;
                        }
                    });
            });
    };

    // Auto-populate country on client selection
    $scope.selectClient = function() {
        var selectedClient = $scope.clients.find(function(client) {
            return client.id == $scope.newRequest.clientAffected;
        });
        if (selectedClient) {
            $scope.newRequest.clientAffected = selectedClient.id;
            $scope.newRequest.clientName = selectedClient.name;
            $scope.newRequest.country = selectedClient.country || selectedClient.Country || '';
            console.log('Selected client:', selectedClient);
        } else {
            $scope.newRequest.clientAffected = '';
            $scope.newRequest.clientName = '';
            $scope.newRequest.country = '';
        }
    };

    // Load projects for autocomplete
    $scope.loadProjects = function() {
        $http.get('apiSheet/change_request.php?action=get_projects')
            .then(function(response) {
                if (response.data.success) {
                    $scope.projects = response.data.data.map(function(project) {
                        return {
                            id: parseInt(project.id),
                            formatted_id: 'PROJ-' + String(project.id).padStart(8, '0')
                        };
                    });
                } else {
                    console.error('Failed to load projects:', response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error loading projects:', error);
            });
    };

    // Load change requests
    $scope.loadRequests = function() {
        $http.get('apiSheet/change_request.php?action=get_requests')
            .then(function(response) {
                if (response.data.success) {
                    $scope.requests = response.data.data;
                    $scope.filterRequests($scope.currentFilter);
                } else {
                    console.error('Failed to load requests:', response.data.message);
                    alert('Failed to load change requests: ' + response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error loading requests:', error);
                alert('An error occurred while loading change requests.');
            });
    };

    // Load specific request details based on requestId
    $scope.loadRequestDetails = function() {
        var requestId = $location.search().requestId;
        if (requestId) {
            $http.get('apiSheet/change_request.php?action=get_request&id=' + requestId)
                .then(function(response) {
                    if (response.data.success) {
                        $scope.selectedRequest = response.data.data;
                        // Ensure time fields are in HH:mm:ss format for <input type="time">
                        $scope.selectedRequest.implementation_start = $scope.selectedRequest.implementation_start || null;
                        $scope.selectedRequest.implementation_end = $scope.selectedRequest.implementation_end || null;
                        // Parse for validation purposes
                        const startTime = $scope.parseDatabaseTime($scope.selectedRequest.implementation_start);
                        const endTime = $scope.parseDatabaseTime($scope.selectedRequest.implementation_end);
                        $scope.selectedRequest.implementation_start_hours = startTime.hours;
                        $scope.selectedRequest.implementation_start_minutes = startTime.minutes;
                        $scope.selectedRequest.implementation_start_period = startTime.period;
                        $scope.selectedRequest.implementation_end_hours = endTime.hours;
                        $scope.selectedRequest.implementation_end_minutes = endTime.minutes;
                        $scope.selectedRequest.implementation_end_period = endTime.period;
                        // Set isImplementationSaved based on valid implementation_status
                        $scope.isImplementationSaved = $scope.selectedRequest.implementation_status && ['Successful', 'Failed', 'Rescheduled'].includes($scope.selectedRequest.implementation_status);
                        $scope.approval.deptHead = {
                            name: $scope.selectedRequest.approval_status.dept_head.comments ? ($scope.selectedRequest.approval_status.dept_head.name || '') : '',
                            date: $scope.selectedRequest.approval_status.dept_head.comments && $scope.selectedRequest.approval_status.dept_head.date ? new Date($scope.selectedRequest.approval_status.dept_head.date) : null,
                            dateFormatted: $scope.selectedRequest.approval_status.dept_head.comments && $scope.selectedRequest.approval_status.dept_head.date ? $scope.formatDateForInput(new Date($scope.selectedRequest.approval_status.dept_head.date)) : '',
                            comments: $scope.selectedRequest.approval_status.dept_head.comments || '',
                            approved: $scope.selectedRequest.approval_status.dept_head.approved
                        };
                        $scope.approval.qa = {
                            name: $scope.selectedRequest.approval_status.qa.comments ? ($scope.selectedRequest.approval_status.qa.name || '') : '',
                            date: $scope.selectedRequest.approval_status.qa.comments && $scope.selectedRequest.approval_status.qa.date ? new Date($scope.selectedRequest.approval_status.qa.date) : null,
                            dateFormatted: $scope.selectedRequest.approval_status.qa.comments && $scope.selectedRequest.approval_status.qa.date ? $scope.formatDateForInput(new Date($scope.selectedRequest.approval_status.qa.date)) : '',
                            comments: $scope.selectedRequest.approval_status.qa.comments || '',
                            approved: $scope.selectedRequest.approval_status.qa.approved
                        };
                        $scope.approval.ceo = {
                            name: $scope.selectedRequest.approval_status.ceo.comments ? ($scope.selectedRequest.approval_status.ceo.name || '') : '',
                            date: $scope.selectedRequest.approval_status.ceo.comments && $scope.selectedRequest.approval_status.ceo.date ? new Date($scope.selectedRequest.approval_status.ceo.date) : null,
                            dateFormatted: $scope.selectedRequest.approval_status.ceo.comments && $scope.selectedRequest.approval_status.ceo.date ? $scope.formatDateForInput(new Date($scope.selectedRequest.approval_status.ceo.date)) : '',
                            comments: $scope.selectedRequest.approval_status.ceo.comments || '',
                            approved: $scope.selectedRequest.approval_status.ceo.approved
                        };
                        $scope.setDefaultApproverNames();
                        $scope.checkAllApprovalsComplete();
                    } else {
                        console.error('Failed to load request:', response.data.message);
                        alert('Failed to load change request: ' + response.data.message);
                    }
                })
                .catch(function(error) {
                    console.error('Error loading request:', error);
                    alert('An error occurred while loading the change request.');
                });
        }
    };

    // Filter requests based on status
    $scope.filterRequests = function(filter) {
        $scope.currentFilter = filter;
        $scope.filterDisplayText = {
            'all': 'All',
            'my_requests': 'My Requests',
            'my_approved': 'My Approved',
            'pending': 'Pending',
            'approved': 'Approved',
            'rejected': 'Rejected',
            'failed': 'Failed',
            'rescheduled': 'Rescheduled',
            'implemented': 'Implemented'
        }[filter] || 'All';

        if (filter === 'all') {
            $scope.filteredRequests = $scope.requests;
        } else if (filter === 'my_requests') {
            $scope.filteredRequests = $scope.requests.filter(function(request) {
                return request.submitted_by == $scope.user_info.user_id;
            });
        } else if (filter === 'my_approved') {
            $http.get('apiSheet/change_request.php?action=get_requests&filter=my_approved&user_id=' + $scope.user_info.user_id)
                .then(function(response) {
                    if (response.data.success) {
                        $scope.filteredRequests = response.data.data;
                        $timeout(function() {
                            $scope.$apply();
                        });
                    } else {
                        console.error('Failed to load my approved requests:', response.data.message);
                        alert('Failed to load my approved requests: ' + response.data.message);
                        $scope.filteredRequests = [];
                    }
                })
                .catch(function(error) {
                    console.error('Error loading my approved requests:', error);
                    alert('An error occurred while loading my approved requests.');
                    $scope.filteredRequests = [];
                });
        } else {
            $http.get('apiSheet/change_request.php?action=get_requests&status=' + filter)
                .then(function(response) {
                    if (response.data.success) {
                        $scope.filteredRequests = response.data.data;
                        $timeout(function() {
                            $scope.$apply();
                        });
                    } else {
                        console.error('Failed to load ' + filter + ' requests:', response.data.message);
                        alert('Failed to load ' + filter + ' requests: ' + response.data.message);
                        $scope.filteredRequests = [];
                    }
                })
                .catch(function(error) {
                    console.error('Error loading ' + filter + ' requests:', error);
                    alert('An error occurred while loading ' + filter + ' requests.');
                    $scope.filteredRequests = [];
                });
        }
    };

    // Get current year short format for Change No.
    $scope.getCurrentYearShort = function() {
        return new Date().getFullYear().toString().slice(-2);
    };

    // Filter projects based on input
    $scope.filterProjects = function(event) {
        var input = $scope.newRequest.projectIdFormatted || '';
        if (input.length >= 3) {
            $scope.filteredProjects = $scope.projects.filter(function(project) {
                return project.formatted_id.toLowerCase().includes(input.toLowerCase());
            });
            $scope.showProjectSuggestions = $scope.filteredProjects.length > 0;
        } else {
            $scope.showProjectSuggestions = false;
            $scope.filteredProjects = [];
        }
    };

    // Select project from autocomplete
    $scope.selectProject = function(project) {
        $scope.newRequest.projectId = project.id;
        $scope.newRequest.projectIdFormatted = project.formatted_id;
        $scope.showProjectSuggestions = false;
        $scope.filteredProjects = [];
    };

    // Submit the change request
    $scope.submitRequest = function() {
        if (!$scope.newRequestForm.$valid) {
            alert('Please fill in all required fields');
            return;
        }

        if (!$scope.newRequest.projectId || isNaN($scope.newRequest.projectId)) {
            alert('Invalid Project ID. Please select a valid project ID from the list.');
            return;
        }

        if (!$scope.projects.some(function(project) { return project.id === $scope.newRequest.projectId; })) {
            alert('Invalid Project ID. Please select a valid project ID from the list.');
            return;
        }

        if (!$scope.user_info.user_id || isNaN($scope.user_info.user_id)) {
            console.error('submitRequest: Invalid user_info.user_id:', $scope.user_info.user_id);
            alert('User ID is missing or invalid. Please log in again.');
            check_auth.logout();
            window.location.href = '/login';
            return;
        }

        if (!$scope.newRequest.requestBy || isNaN($scope.newRequest.requestBy)) {
            console.error('submitRequest: Invalid newRequest.requestBy:', $scope.newRequest.requestBy);
            alert('Please select a valid user for Request By.');
            return;
        }

        if (!$scope.users.some(function(user) { return user.id === $scope.newRequest.requestBy; })) {
            console.error('submitRequest: requestBy not found in users:', $scope.newRequest.requestBy);
            alert('Invalid Request By user. Please select a valid user from the list.');
            return;
        }

        if (!$scope.newRequest.implementer || isNaN($scope.newRequest.implementer)) {
            console.error('submitRequest: Invalid newRequest.implementer:', $scope.newRequest.implementer);
            alert('Please select a valid user for Implementer.');
            return;
        }

        if (!$scope.users.some(function(user) { return user.id === $scope.newRequest.implementer; })) {
            console.error('submitRequest: implementer not found in users:', $scope.newRequest.implementer);
            alert('Invalid Implementer user. Please select a valid user from the list.');
            return;
        }

        if (!$scope.newRequest.clientName) {
            alert('Please select a valid client.');
            return;
        }

        $scope.isSubmitting = true;

        var requestData = angular.copy($scope.newRequest);

        if (requestData.dateRaised) {
            requestData.dateRaised = new Date(requestData.dateRaised).toISOString().split('T')[0];
        }
        if (requestData.implementationDate) {
            requestData.implementationDate = new Date(requestData.implementationDate).toISOString().split('T')[0];
        }

        if (requestData.startTime) {
            requestData.startTime = new Date(requestData.startTime).toISOString().substr(11, 8);
        }
        if (requestData.endTime) {
            requestData.endTime = new Date(requestData.endTime).toISOString().substr(11, 8);
        }

        requestData.request_by = parseInt($scope.newRequest.requestBy);
        requestData.implementer = parseInt($scope.newRequest.implementer);
        requestData.submitted_by = parseInt($scope.user_info.user_id);

        var mappedData = {
            project_id: requestData.projectId,
            type: requestData.type,
            emergency_reason: requestData.emergencyReason || null,
            date_raised: requestData.dateRaised,
            client_affected: requestData.clientName,
            country: requestData.country,
            request_by: requestData.request_by,
            implementer: requestData.implementer,
            priority: requestData.priority,
            title: requestData.title,
            description: requestData.description,
            implementation_date: requestData.implementationDate,
            start_time: requestData.startTime || null,
            end_time: requestData.endTime || null,
            change_reason: requestData.changeReason,
            impact_assessment: requestData.impactAssessment,
            service_application: requestData.serviceApplication,
            affected_artifacts: requestData.affectedArtifacts,
            scope: requestData.scope,
            implementation_plan: requestData.implementationPlan,
            budget: requestData.budget || null,
            risk: requestData.risk || null,
            backout_plan: requestData.backoutPlan,
            resources_required: requestData.resourcesRequired || null,
            comments: requestData.comments || null,
            submitted_by: requestData.submitted_by
        };

        console.log('Submitting data:', mappedData);

        $http({
                method: 'POST',
                url: 'apiSheet/change_request.php?action=create_request',
                data: mappedData,
                headers: { 'Content-Type': 'application/json' }
            })
            .then(function(response) {
                $scope.isSubmitting = false;
                if (response.data.success) {
                    $scope.successProjectId = 'PROJ-' + String(response.data.project_id).padStart(8, '0');
                    $scope.successChangeNo = response.data.change_no;
                    $scope.showSuccessModal = true;
                    $scope.newRequest = {
                        submitted_by: $scope.user_info.user_id,
                        requestBy: $scope.user_info.user_id,
                        implementer: $scope.user_info.user_id,
                        projectId: null,
                        projectIdFormatted: ''
                    };
                    $scope.newRequestForm.$setPristine();
                    $scope.newRequestForm.$setUntouched();
                    $scope.loadRequests();
                    // Check for email sending issues
                    if (response.data.message.includes('but some emails failed')) {
                        console.warn('Email sending issue:', response.data.message);
                        alert('Request submitted successfully, but some approval emails failed to send. Please contact support.');
                    }
                } else {
                    alert('Failed to submit request: ' + response.data.message);
                }
            })
            .catch(function(error) {
                $scope.isSubmitting = false;
                console.error('Error submitting request:', error);
                alert('An error occurred while submitting the request: ' + (error.data ? error.data.message : 'Unknown error'));
            });
    };

    // Approve request
    $scope.approveRequest = function() {
        var approvalLevel = '';
        var approverId = null;
        var comments = '';

        if (!$scope.selectedRequest.approval_status.dept_head.approved && !$scope.selectedRequest.approval_status.dept_head.comments) {
            approvalLevel = 'dept_head';
            if (!$scope.isUserEligible('dept_head')) {
                alert('You are not authorized to approve as Department Head or you have already approved at another level.');
                return;
            }
            approverId = $scope.user_info.user_id;
            comments = $scope.approval.deptHead.comments || '';
        } else if ($scope.selectedRequest.approval_status.dept_head.approved && !$scope.selectedRequest.approval_status.qa.comments) {
            approvalLevel = 'qa';
            if (!$scope.isUserEligible('qa')) {
                alert('You are not authorized to approve as Quality Assurance or you have already approved at another level.');
                return;
            }
            approverId = $scope.user_info.user_id;
            comments = $scope.approval.qa.comments || '';
        } else if ($scope.selectedRequest.approval_status.qa.approved && !$scope.selectedRequest.approval_status.ceo.comments) {
            approvalLevel = 'ceo';
            if (!$scope.isUserEligible('ceo')) {
                alert('You are not authorized to approve as CEO/Senior Manager or you have already approved at another level.');
                return;
            }
            approverId = $scope.user_info.user_id;
            comments = $scope.approval.ceo.comments || '';
        } else {
            alert('No further approvals required or already approved/rejected.');
            return;
        }

        if (!approverId) {
            console.error('No valid approver ID found for', approvalLevel);
            alert('Invalid approver ID.');
            return;
        }

        var approvalData = {
            request_id: $scope.selectedRequest.id,
            approver_id: approverId,
            approval_level: approvalLevel,
            status: 'approved',
            comments: comments || null
        };

        console.log('Sending approval data:', approvalData);

        $http({
                method: 'POST',
                url: 'apiSheet/change_request.php?action=update_approval',
                data: approvalData,
                headers: { 'Content-Type': 'application/json' }
            })
            .then(function(response) {
                if (response.data.success) {
                    alert('Approval saved successfully.');
                    $scope.loadRequestDetails();
                    $scope.loadRequests();
                    $scope.checkAllApprovalsComplete();
                } else {
                    alert('Failed to save approval: ' + response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error saving approval:', error);
                alert('An error occurred while saving the approval.');
            });
    };

    // Reject request
    $scope.rejectRequest = function() {
        var approvalLevel = '';
        var approverId = null;
        var comments = '';

        if (!$scope.selectedRequest.approval_status.dept_head.approved && !$scope.selectedRequest.approval_status.dept_head.comments) {
            approvalLevel = 'dept_head';
            if (!$scope.isUserEligible('dept_head')) {
                alert('You are not authorized to reject as Department Head or you have already approved at another level.');
                return;
            }
            approverId = $scope.user_info.user_id;
            comments = $scope.approval.deptHead.comments || '';
        } else if ($scope.selectedRequest.approval_status.dept_head.approved && !$scope.selectedRequest.approval_status.qa.comments) {
            approvalLevel = 'qa';
            if (!$scope.isUserEligible('qa')) {
                alert('You are not authorized to reject as Quality Assurance or you have already approved at another level.');
                return;
            }
            approverId = $scope.user_info.user_id;
            comments = $scope.approval.qa.comments || '';
        } else if ($scope.selectedRequest.approval_status.qa.approved && !$scope.selectedRequest.approval_status.ceo.comments) {
            approvalLevel = 'ceo';
            if (!$scope.isUserEligible('ceo')) {
                alert('You are not authorized to reject as CEO/Senior Manager or you have already approved at another level.');
                return;
            }
            approverId = $scope.user_info.user_id;
            comments = $scope.approval.ceo.comments || '';
        } else {
            alert('No further approvals required or already approved/rejected.');
            return;
        }

        if (!approverId) {
            console.error('No valid approver ID found for', approvalLevel);
            alert('Invalid approver ID.');
            return;
        }

        var approvalData = {
            request_id: $scope.selectedRequest.id,
            approver_id: approverId,
            approval_level: approvalLevel,
            status: 'rejected',
            comments: comments || null
        };

        console.log('Sending rejection data:', approvalData);

        $http({
                method: 'POST',
                url: 'apiSheet/change_request.php?action=update_approval',
                data: approvalData,
                headers: { 'Content-Type': 'application/json' }
            })
            .then(function(response) {
                if (response.data.success) {
                    alert('Rejection saved successfully.');
                    $scope.loadRequestDetails();
                    $scope.loadRequests();
                    $scope.checkAllApprovalsComplete();
                } else {
                    alert('Failed to save rejection: ' + response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error saving rejection:', error);
                alert('An error occurred while saving the rejection.');
            });
    };

    // Save changes for implementation status and duration
    $scope.saveChanges = function() {
        if (!$scope.selectedRequest.id) {
            alert('No request selected.');
            return;
        }

        if (!$scope.allApprovalsComplete) {
            alert('Cannot save implementation details until all approval stages are complete.');
            return;
        }

        if (!$scope.selectedRequest.implementation_status) {
            alert('Please select an implementation status.');
            return;
        }

        if ($scope.selectedRequest.implementation_start_hours &&
            ($scope.selectedRequest.implementation_start_hours < 1 || $scope.selectedRequest.implementation_start_hours > 12)) {
            alert('Start hours must be between 1 and 12');
            return;
        }
        if ($scope.selectedRequest.implementation_start_minutes &&
            ($scope.selectedRequest.implementation_start_minutes < 0 || $scope.selectedRequest.implementation_start_minutes > 59)) {
            alert('Start minutes must be between 0 and 59');
            return;
        }
        if ($scope.selectedRequest.implementation_end_hours &&
            ($scope.selectedRequest.implementation_end_hours < 1 || $scope.selectedRequest.implementation_end_hours > 12)) {
            alert('End hours must be between 1 and 12');
            return;
        }
        if ($scope.selectedRequest.implementation_end_minutes &&
            ($scope.selectedRequest.implementation_end_minutes < 0 || $scope.selectedRequest.implementation_end_minutes > 59)) {
            alert('End minutes must be between 0 and 59');
            return;
        }

        var updateData = {
            request_id: $scope.selectedRequest.id,
            implementation_status: $scope.selectedRequest.implementation_status,
            implementation_start: $scope.formatTimeForDatabase(
                $scope.selectedRequest.implementation_start_hours,
                $scope.selectedRequest.implementation_start_minutes,
                $scope.selectedRequest.implementation_start_period
            ) || $scope.selectedRequest.implementation_start,
            implementation_end: $scope.formatTimeForDatabase(
                $scope.selectedRequest.implementation_end_hours,
                $scope.selectedRequest.implementation_end_minutes,
                $scope.selectedRequest.implementation_end_period
            ) || $scope.selectedRequest.implementation_end,
            implementation_notes: $scope.selectedRequest.implementation_notes || null
        };

        $http({
                method: 'POST',
                url: 'apiSheet/change_request.php?action=update_implementation',
                data: updateData,
                headers: { 'Content-Type': 'application/json' }
            })
            .then(function(response) {
                if (response.data.success) {
                    alert('Implementation status updated successfully.');
                    // Update UI with returned values
                    $scope.selectedRequest.implementation_status = response.data.data.implementation_status;
                    $scope.selectedRequest.implementation_start = response.data.data.implementation_start || null;
                    $scope.selectedRequest.implementation_end = response.data.data.implementation_end || null;
                    $scope.selectedRequest.implementation_notes = response.data.data.implementation_notes || null;
                    // Re-parse for validation
                    const startTime = $scope.parseDatabaseTime($scope.selectedRequest.implementation_start);
                    const endTime = $scope.parseDatabaseTime($scope.selectedRequest.implementation_end);
                    $scope.selectedRequest.implementation_start_hours = startTime.hours;
                    $scope.selectedRequest.implementation_start_minutes = startTime.minutes;
                    $scope.selectedRequest.implementation_start_period = startTime.period;
                    $scope.selectedRequest.implementation_end_hours = endTime.hours;
                    $scope.selectedRequest.implementation_end_minutes = endTime.minutes;
                    $scope.selectedRequest.implementation_end_period = endTime.period;
                    // Set isImplementationSaved based on valid status
                    $scope.isImplementationSaved = ['Successful', 'Failed', 'Rescheduled'].includes($scope.selectedRequest.implementation_status);
                    $scope.loadRequests();
                } else {
                    alert('Failed to update implementation status: ' + response.data.message);
                }
            })
            .catch(function(error) {
                console.error('Error updating implementation status:', error);
                alert('An error occurred while updating the implementation status.');
            });
    };

    // Download PDF - Professional layout with autoTable
    $scope.downloadPDF = function() {
        try {
            if (!$scope.selectedRequest.id) {
                alert('No request selected to download.');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            // Define styling constants
            const primaryColor = '#5e72e4';
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const margin = 10;
            const maxWidth = pageWidth - 2 * margin;

            // Add header to every page
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text('[Company Logo]', margin, 15); // Placeholder for logo
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(16);
            doc.setTextColor(primaryColor);
            doc.text(`Change Request: ${$scope.selectedRequest.change_no || 'CH' + $scope.getCurrentYearShort() + ($scope.selectedRequest.id ? $scope.selectedRequest.id.toString().padStart(6, '0') : 'N/A')}`, margin, 25);
            doc.setLineWidth(0.5);
            doc.setDrawColor(primaryColor);
            doc.line(margin, 30, pageWidth - margin, 30);

            // Add footer to every page
            const totalPages = doc.internal.getNumberOfPages();
            for (let i = 1; i <= totalPages; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(100);
                doc.text(`Page ${i} of ${totalPages}`, pageWidth - margin - 20, pageHeight - 10);
                doc.text(`Generated on: ${new Date().toLocaleDateString()}`, margin, pageHeight - 10);
            }

            // Helper function to add a section title
            function addSectionTitle(title) {
                doc.setFontSize(14);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(primaryColor);
                doc.text(title, margin, doc.autoTable.previous.finalY + 10);
                doc.setLineWidth(0.3);
                doc.line(margin, doc.autoTable.previous.finalY + 12, pageWidth - margin, doc.autoTable.previous.finalY + 12);
            }

            // Basic Information Section
            doc.autoTable({
                startY: 35,
                head: [
                    ['Field', 'Value']
                ],
                body: [
                    ['Type of Change', $scope.selectedRequest.type || 'N/A'],
                    ['Reason for Emergency', $scope.selectedRequest.emergency_reason || 'N/A'],
                    ['Date Raised', $scope.selectedRequest.date_raised_formatted || 'N/A'],
                    ['Global / Customer Name', $scope.selectedRequest.client_name || 'N/A'],
                    ['Country', $scope.selectedRequest.country || 'N/A'],
                    ['Request No', $scope.selectedRequest.project_id ? 'PROJ-' + $scope.selectedRequest.project_id.toString().padStart(8, '0') : 'N/A'],
                    ['Request By', $scope.selectedRequest.request_by_name || 'N/A'],
                    ['Priority', $scope.selectedRequest.priority || 'N/A']
                ],
                styles: {
                    font: 'Inter',
                    fontSize: 10,
                    cellPadding: 3,
                    overflow: 'linebreak',
                    valign: 'middle'
                },
                headStyles: {
                    fillColor: primaryColor,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: { cellWidth: 60 },
                    1: { cellWidth: maxWidth - 60 }
                },
                margin: { top: 35, left: margin, right: margin },
                didDrawPage: function(data) {
                    // Reset header for subsequent pages
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(10);
                    doc.setTextColor(100);
                    doc.text('[Company Logo]', margin, 15); // Placeholder for logo
                    doc.setFont('helvetica', 'bold');
                    doc.setFontSize(16);
                    doc.setTextColor(primaryColor);
                    doc.text(`Change Request: ${$scope.selectedRequest.change_no || 'CH' + $scope.getCurrentYearShort() + ($scope.selectedRequest.id ? $scope.selectedRequest.id.toString().padStart(6, '0') : 'N/A')}`, margin, 25);
                    doc.setLineWidth(0.5);
                    doc.line(margin, 30, pageWidth - margin, 30);
                }
            });

            // Change Details Section
            addSectionTitle('Change Details');
            doc.autoTable({
                startY: doc.autoTable.previous.finalY + 15,
                head: [
                    ['Field', 'Value']
                ],
                body: [
                    ['Change Name', $scope.selectedRequest.title || 'N/A'],
                    ['Description of Change', $scope.selectedRequest.description || 'N/A'],
                    ['Implementation Date Required', $scope.selectedRequest.implementation_date_formatted || 'N/A'],
                    ['Implementation Start Time', $scope.selectedRequest.start_time || 'N/A'],
                    ['Implementation End Time', $scope.selectedRequest.end_time || 'N/A'],
                    ['Implementer', $scope.selectedRequest.implementer_name || 'N/A'],
                    ['Reason for Change', $scope.selectedRequest.change_reason || 'N/A']
                ],
                styles: {
                    font: 'Inter',
                    fontSize: 10,
                    cellPadding: 3,
                    overflow: 'linebreak',
                    valign: 'middle'
                },
                headStyles: {
                    fillColor: primaryColor,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: { cellWidth: 60 },
                    1: { cellWidth: maxWidth - 60 }
                },
                margin: { left: margin, right: margin }
            });

            // Impact Assessment Section
            addSectionTitle('Impact Assessment');
            doc.autoTable({
                startY: doc.autoTable.previous.finalY + 15,
                head: [
                    ['Field', 'Value']
                ],
                body: [
                    ['Change Impact Assessment', $scope.selectedRequest.impact_assessment || 'N/A'],
                    ['Service/Application', $scope.selectedRequest.service_application || 'N/A'],
                    ['Affected Artifacts', $scope.selectedRequest.affected_artifacts || 'N/A'],
                    ['Components', $scope.selectedRequest.scope || 'N/A'],
                    ['Implementation Plan', $scope.selectedRequest.implementation_plan || 'N/A'],
                    ['Budget', $scope.selectedRequest.budget || 'N/A'],
                    ['Risk', $scope.selectedRequest.risk || 'N/A'],
                    ['Back out Plan', $scope.selectedRequest.backout_plan || 'N/A'],
                    ['Resources Required', $scope.selectedRequest.resources_required || 'N/A'],
                    ['Other Comments', $scope.selectedRequest.comments || 'N/A']
                ],
                styles: {
                    font: 'Inter',
                    fontSize: 10,
                    cellPadding: 3,
                    overflow: 'linebreak',
                    valign: 'middle'
                },
                headStyles: {
                    fillColor: primaryColor,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: { cellWidth: 60 },
                    1: { cellWidth: maxWidth - 60 }
                },
                margin: { left: margin, right: margin }
            });

            // Approval Details Section
            addSectionTitle('Approval Details');
            doc.autoTable({
                startY: doc.autoTable.previous.finalY + 15,
                head: [
                    ['Approved By', 'Name', 'Date', 'Comments', 'Status']
                ],
                body: [
                    [
                        'Department Head',
                        $scope.approval.deptHead.comments ? ($scope.approval.deptHead.name || 'N/A') : 'N/A',
                        $scope.approval.deptHead.comments ? ($scope.approval.deptHead.dateFormatted || 'N/A') : 'N/A',
                        $scope.approval.deptHead.comments || 'N/A',
                        $scope.approval.deptHead.comments ? ($scope.approval.deptHead.approved ? 'Approved' : 'Rejected') : 'Pending'
                    ],
                    [
                        'Quality Assurance',
                        $scope.approval.qa.comments ? ($scope.approval.qa.name || 'N/A') : 'N/A',
                        $scope.approval.qa.comments ? ($scope.approval.qa.dateFormatted || 'N/A') : 'N/A',
                        $scope.approval.qa.comments || 'N/A',
                        $scope.approval.qa.comments ? ($scope.approval.qa.approved ? 'Approved' : 'Rejected') : 'Pending'
                    ],
                    [
                        'CEO/Senior Manager',
                        $scope.approval.ceo.comments ? ($scope.approval.ceo.name || 'N/A') : 'N/A',
                        $scope.approval.ceo.comments ? ($scope.approval.ceo.dateFormatted || 'N/A') : 'N/A',
                        $scope.approval.ceo.comments || 'N/A',
                        $scope.approval.ceo.comments ? ($scope.approval.ceo.approved ? 'Approved' : 'Rejected') : 'Pending'
                    ]
                ],
                styles: {
                    font: 'Inter',
                    fontSize: 10,
                    cellPadding: 3,
                    overflow: 'linebreak',
                    valign: 'middle'
                },
                headStyles: {
                    fillColor: primaryColor,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: { cellWidth: 40 },
                    1: { cellWidth: 40 },
                    2: { cellWidth: 30 },
                    3: { cellWidth: 50 },
                    4: { cellWidth: 30 }
                },
                margin: { left: margin, right: margin }
            });

            // Implementation Status and Duration Section
            addSectionTitle('Implementation Status and Duration');
            doc.autoTable({
                startY: doc.autoTable.previous.finalY + 15,
                head: [
                    ['Field', 'Value']
                ],
                body: [
                    ['Implementation Status', $scope.selectedRequest.implementation_status || 'N/A'],
                    ['Duration Start', $scope.selectedRequest.implementation_start || 'N/A'],
                    ['Duration End', $scope.selectedRequest.implementation_end || 'N/A'],
                    ['Implementation Notes', $scope.selectedRequest.implementation_notes || 'N/A']
                ],
                styles: {
                    font: 'Inter',
                    fontSize: 10,
                    cellPadding: 3,
                    overflow: 'linebreak',
                    valign: 'middle'
                },
                headStyles: {
                    fillColor: primaryColor,
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                columnStyles: {
                    0: { cellWidth: 60 },
                    1: { cellWidth: maxWidth - 60 }
                },
                margin: { left: margin, right: margin }
            });

            // Save the PDF
            const fileName = `Change_Request_${$scope.selectedRequest.change_no || 'CH' + $scope.getCurrentYearShort() + ($scope.selectedRequest.id ? $scope.selectedRequest.id.toString().padStart(6, '0') : 'Details')}.pdf`;
            doc.save(fileName);

            console.log('PDF generated successfully:', fileName);
        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('An error occurred while generating the PDF: ' + error.message + '. Please check the console for details or contact support.');
        }
    };

    // Download table as Excel - Fixed version
    $scope.downloadExcel = function() {
        try {
            console.log('Starting Excel download process...');

            // Check if there's data to export
            if (!$scope.filteredRequests || $scope.filteredRequests.length === 0) {
                alert('No data available to export.');
                return;
            }

            // Define table headers
            const headers = [
                'Change No.',
                'Request No.',
                'Requestor',
                'Date Submitted',
                'Priority',
                'Client Affected',
                'Country',
                'Status'
            ];

            // Prepare data from filteredRequests
            const data = $scope.filteredRequests.map(function(request) {
                return [
                    'CH' + $scope.getCurrentYearShort() + (request.id ? request.id.toString().padStart(6, '0') : ''),
                    request.project_id ? 'PROJ-' + request.project_id.toString().padStart(8, '0') : '',
                    request.request_by_name || '',
                    request.date_submitted_formatted || '',
                    request.priority || '',
                    request.client_name || '',
                    request.country || '',
                    request.status || ''
                ];
            });

            // Create workbook
            const wb = XLSX.utils.book_new();

            // Create worksheet with headers
            const wsData = [headers].concat(data);
            const ws = XLSX.utils.aoa_to_sheet(wsData);

            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Change Requests');

            // Generate Excel file
            const excelBuffer = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });

            // Create blob and download
            const blob = new Blob([excelBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const fileName = 'Change_Requests_' + new Date().toISOString().slice(0, 10) + '.xlsx';

            // Create download link
            const link = document.createElement('a');
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', fileName);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            } else {
                alert('Your browser does not support automatic downloads. Please try a different browser.');
            }

            console.log('Excel file downloaded successfully');
        } catch (error) {
            console.error('Error generating Excel file:', error);
            alert('An error occurred while generating the Excel file. Please try again or contact support.');
        }
    };

    // Go back to change request page
    $scope.goBack = function() {
        window.location.href = 'change_request';
    };

    // Close success modal and refresh page
    $scope.closeSuccessModal = function() {
        $scope.showSuccessModal = false;
        window.location.href = 'change_request';
    };

    // Initialize the controller
    $scope.init = function() {
        $scope.loadUserInfo();
        $scope.loadReferenceData();
        $scope.loadClients();
        $scope.loadUsers();
        $scope.loadProjects();
        $scope.loadRequests();
        $scope.loadRequestDetails();
    };

    $scope.init();
}]);