sheetApp.controller("TasksAnalysisCtrl", function($scope, $http, $location, $timeout) {
    // Initialize
    $scope.analysisType = $location.search().type || 'individual_tasks';
    $scope.fromDate = new Date($location.search().start_date || new Date(new Date().getFullYear(), 0, 1));
    $scope.toDate = new Date($location.search().end_date || new Date());
    $scope.searchQuery = '';
    $scope.filteredTasks = [];

    // Get title based on analysis type
    $scope.getAnalysisTitle = function() {
        switch ($scope.analysisType) {
            case 'individual_tasks':
                return 'Tasks Analysis on Individuals';
            case 'department_performance':
                return 'Department Performance';
            case 'task_timelines':
                return 'Task Timelines';
            case 'completion_metrics':
                return 'Completion Metrics';
            default:
                return 'Analysis';
        }
    };

    // Format date for API
    $scope.formatDateForAPI = function(date) {
        return date ? new Date(date).toISOString().split('T')[0] : '';
    };

    // Fetch data based on analysis type
    $scope.fetchAnalysisData = function() {
        var params = {
            start_date: $scope.formatDateForAPI($scope.fromDate),
            end_date: $scope.formatDateForAPI($scope.toDate),
            type: $scope.analysisType
        };

        $http.get('apiSheet/get_analysis_data.php', { params: params })
            .then(function(response) {
                // Process response data
                if (response.data.success) {
                    $scope.tasks = response.data.data;
                    $scope.filterTasks(); // Apply search filter after fetching data
                    console.log('Analysis data loaded:', $scope.tasks); // Debug
                } else {
                    console.error('API error:', response.data.message);
                    $scope.tasks = [];
                    $scope.filteredTasks = [];
                }
            }, function(error) {
                console.error('Error fetching analysis data:', error);
                $scope.tasks = [];
                $scope.filteredTasks = [];
            });
    };

    // Filter tasks based on search query
    $scope.filterTasks = function() {
        if ($scope.analysisType !== 'individual_tasks') {
            $scope.filteredTasks = $scope.tasks;
            return;
        }

        var query = ($scope.searchQuery || '').toLowerCase();
        if (!query) {
            $scope.filteredTasks = $scope.tasks;
            return;
        }

        $scope.filteredTasks = $scope.tasks.filter(function(task) {
            return (
                (task.user || '').toLowerCase().includes(query) ||
                (task.department || '').toLowerCase().includes(query) ||
                (task.role || '').toLowerCase().includes(query) ||
                (task.email || '').toLowerCase().includes(query)
            );
        });
    };

    // Initialize
    $scope.fetchAnalysisData();
});