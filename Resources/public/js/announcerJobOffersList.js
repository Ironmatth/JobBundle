(function () {
    'use strict';
    
    var announcerId = $('#announcer-job-offers-list-datas-box').data('announcer-id');
    
    $('#announcer-job-offers-list-body').on('click', '.delete-job-offer-btn', function () {
        var jobOfferId = $(this).data('job-offer-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_job_job_offer_delete',
                {'jobOffer': jobOfferId}
            ),
            removeJobOfferRow,
            jobOfferId,
            Translator.trans('delete_job_offer_message', {}, 'job'),
            Translator.trans('delete_job_offer', {}, 'job')
        );
    });
    
    var removeJobOfferRow = function (event, jobOfferId) {
        $('#job-offer-row-' + jobOfferId).remove();
    };
})();