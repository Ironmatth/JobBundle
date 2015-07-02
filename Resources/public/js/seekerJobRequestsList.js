(function () {
    'use strict';
    
    $('#seeker-job-requests-list-body').on('click', '.delete-job-request-btn', function () {
        var jobRequestId = $(this).data('job-request-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_job_job_request_delete',
                {'jobRequest': jobRequestId}
            ),
            removeJobRequestRow,
            jobRequestId,
            Translator.trans('delete_job_request_message', {}, 'job'),
            Translator.trans('delete_job_request', {}, 'job')
        );
    });
    
    var removeJobRequestRow = function (event, jobRequestId) {
        $('#job-request-row-' + jobRequestId).remove();
    };
})();