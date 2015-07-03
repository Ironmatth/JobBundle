(function () {
    'use strict';
    
    $('#pending-announcers-management-body').on('click', '.accept-pending-user-btn', function () {
        var pendingAnnouncerId = $(this).data('pending-announcer-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_job_pending_announcer_accept',
                {'pendingAnnouncer': pendingAnnouncerId}
            ),
            removePendingAnnouncerRow,
            pendingAnnouncerId,
            Translator.trans('accept_pending_announcer_message', {}, 'job'),
            Translator.trans('accept_pending_announcer', {}, 'job')
        );
    });
    
    $('#pending-announcers-management-body').on('click', '.decline-pending-user-btn', function () {
        var pendingAnnouncerId = $(this).data('pending-announcer-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_job_pending_announcer_decline',
                {'pendingAnnouncer': pendingAnnouncerId}
            ),
            removePendingAnnouncerRow,
            pendingAnnouncerId,
            Translator.trans('decline_pending_announcer_message', {}, 'job'),
            Translator.trans('decline_pending_announcer', {}, 'job')
        );
    });
    
    var removePendingAnnouncerRow = function (event, pendingAnnouncerId) {
        $('#pending-announcer-row-' + pendingAnnouncerId).remove();
    };
})();