(function () {
    'use strict';
    
    $('#announcers-management-body').on('click', '.delete-announcer-btn', function () {
        var announcerId = $(this).data('announcer-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_job_announcer_delete',
                {'announcer': announcerId}
            ),
            removeAnnouncerRow,
            announcerId,
            Translator.trans('delete_announcer_message', {}, 'job'),
            Translator.trans('delete_announcer', {}, 'job')
        );
    });
    
    $('#announcers-management-body').on('click', '#create-announcer-btn', function () {
        var communityId = $(this).data('community-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibre_job_announcers_create_form',
                {'community': communityId}
            ),
            refreshPage,
            function() {}
        );
    });
    
    var refreshPage = function () {
        window.location.reload();
    };
    
    var removeAnnouncerRow = function (event, announcerId) {
        $('#announcer-row-' + announcerId).remove();
    };
})();