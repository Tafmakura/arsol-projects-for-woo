// JS for Arsol Active Projects Admin
(function($) {
    'use strict';

    var ArsolActiveProject = {
        init: function() {
            this.bindEvents();
            this.initializeComponents();
        },

        bindEvents: function() {
            // Add any project-specific event handlers here
            // For example, status changes, date updates, etc.
            this.handleStatusChanges();
            this.handleDateValidation();
        },

        initializeComponents: function() {
            // Initialize any components specific to active projects
            this.initializeDatePickers();
            this.initializeStatusDropdowns();
        },

        handleStatusChanges: function() {
            // Handle project status changes
            $('select[name="project_status"]').on('change', function() {
                var newStatus = $(this).val();
                var $form = $(this).closest('form');
                
                // You can add status-specific logic here
                if (newStatus === 'completed') {
                    // Maybe auto-set completion date
                    var today = new Date().toISOString().split('T')[0];
                    $('input[name="project_completion_date"]').val(today);
                }
            });
        },

        handleDateValidation: function() {
            // Handle date validation
            $('input[type="date"]').on('change', function() {
                var $this = $(this);
                var selectedDate = new Date($this.val());
                var today = new Date();
                
                // Add date validation logic as needed
                if ($this.attr('name') === 'project_due_date' && selectedDate < today) {
                    // Optional: warn about past due dates
                    console.log('Due date is in the past');
                }
            });
        },

        initializeDatePickers: function() {
            // Initialize any custom date picker functionality
            // This is a placeholder for future enhancements
        },

        initializeStatusDropdowns: function() {
            // Initialize any custom dropdown functionality
            // This is a placeholder for future enhancements
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Only initialize on project admin pages
        if ($('body').hasClass('post-type-arsol-pfw-project') || $('#project_details_meta_box').length > 0) {
            ArsolActiveProject.init();
        }
    });

})(jQuery);