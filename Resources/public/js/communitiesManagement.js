(function () {
    'use strict';
    
    $('#communities-management-body').on('click', '#add-community-btn', function () {
        
        window.Claroline.Modal.displayForm(
            Routing.generate('formalibre_job_admin_community_create_form'),
            refreshPage,
            function() {}
        );
    });
    
    $('#communities-management-body').on('click', '.edit-community-btn', function () {
        var communityId = $(this).data('community-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibre_job_admin_community_edit_form',
                {'community': communityId}
            ),
            refreshPage,
            function() {}
        );
    });
    
    $('#communities-management-body').on('click', '.delete-community-btn', function () {
        var communityId = $(this).data('community-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_job_admin_community_delete',
                {'community': communityId}
            ),
            removeCommunityRow,
            communityId,
            Translator.trans('delete_community_message', {}, 'job'),
            Translator.trans('delete_community', {}, 'job')
        );
    });
    
    var refreshPage = function () {
        window.location.reload();
    };
    
    var removeCommunityRow = function (event, communityId) {
        $('#community-row-' + communityId).remove();
    };
})();