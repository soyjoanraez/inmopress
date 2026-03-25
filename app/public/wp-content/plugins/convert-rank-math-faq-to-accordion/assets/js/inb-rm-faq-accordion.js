(function($) {
    $(document).ready(function () {
        var rm_faq_block = $('#rank-math-faq .rank-math-list-item');
        var rm_faq_answer = $('#rank-math-faq .rank-math-answer');
       
        // Convert FAQ questions to buttons and set up accessibility attributes
        rm_faq_block.each(function () {
            var rm_faq_id = $(this).attr('id').split("-")[2];
            var rm_question_inner_html = $(this).find('h3').html();
           
            $(this).find('h3').html('<button class="rm-faq-question-btn">' + rm_question_inner_html + '</button>');
            $(this).find('button').attr('id', 'rm-accordion-' + rm_faq_id);
            $(this).find('button').attr('aria-controls', 'rm-accordion-panel-' + rm_faq_id);
            $(this).find('button').attr('aria-expanded', 'false');
        });
       
        // Set up answer panels with accessibility attributes
        rm_faq_answer.each(function () {
            var rm_faq_id = $(this).closest('.rank-math-list-item').attr('id').split("-")[2];
            $(this).attr('id', 'rm-accordion-panel-' + rm_faq_id);
            $(this).attr('aria-labelledby', 'rm-accordion-' + rm_faq_id);
        });

        // Check if initial expand first FAQ is enabled and expand the first item
        if (typeof inbrmfa_settings !== 'undefined' && inbrmfa_settings.initial_expand_first == '1') {
            var first_faq_question = $('#rank-math-faq .rank-math-question').first();
            if (first_faq_question.length) {
                first_faq_question.addClass('rm-faq-question-active');
                first_faq_question.siblings('.rank-math-answer').addClass('rm-faq-answer-active').show();
                first_faq_question.find('button').attr('aria-expanded', 'true');
            }
        }
       
        // Handle FAQ question clicks
        $('#rank-math-faq .rank-math-question').on('click', function () {
            var rm_schema_faq_question = $('#rank-math-faq .rank-math-question');
           
            // Close all other FAQ items
            rm_schema_faq_question.not(this).removeClass('rm-faq-question-active');
            rm_schema_faq_question.not(this).siblings('.rank-math-answer').removeClass('rm-faq-answer-active').slideUp();
            rm_schema_faq_question.not(this).find('button').attr('aria-expanded', 'false');
           
            // Toggle current FAQ item
            if ($(this).siblings('.rank-math-answer').is(':visible')) {
                $(this).removeClass('rm-faq-question-active');
                $(this).siblings('.rank-math-answer').removeClass('rm-faq-answer-active').slideUp();
                $(this).find('button').attr('aria-expanded', 'false');
            } else {
                $(this).addClass('rm-faq-question-active');
                $(this).siblings('.rank-math-answer').addClass('rm-faq-answer-active').slideDown();
                $(this).find('button').attr('aria-expanded', 'true');
            }
        });
    });
})(jQuery);