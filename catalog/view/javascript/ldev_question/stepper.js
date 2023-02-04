$(function () {
    $('body').delegate('.ldev-question a.panel-heading','click',function (e) {
        e.preventDefault()
    })
});
function Stepper(steps) {

    this.stepsWrap = $(steps);
    this.stepContents = $(steps).find('[data-step-content]');
    this.navButtonNext = $(steps).find('[data-control="step"][data-direction="next"]');
    this.navButtonPrev = $(steps).find('[data-control="step"][data-direction="prev"]');
    this.panelSteps = $(steps).find('[data-toggle="step"]');
    this.activeStepIndex = 0;
    this.stepsAmount = $(steps).find('[data-step-content]').length;

    var self = this;


   this.goNextStep = function(e){
       if($(e.currentTarget).hasClass('disabled')) return;
       self.activeStepIndex++;
       var direction = $(e.currentTarget).attr('data-direction');

        self.disactivateSteps();
        self.activateStepByIndex(self.activeStepIndex, direction)

   };
    this.goPrevStep= function(e){
        if($(e.currentTarget).hasClass('disabled')) return;
        self.activeStepIndex--;
        var direction = $(e.currentTarget).attr('data-direction');

        self.disactivateSteps();
        self.activateStepByIndex(self.activeStepIndex, direction)

    };

    this.disactivateSteps = function() {

        $(self.panelSteps.closest('li')).removeClass('active');
        self.stepContents.removeClass('active');
    };
    this.activateStepByIndex = function(index, direction) {
        self.navButtonNext.removeClass('disabled');
        self.navButtonPrev.removeClass('disabled');

        if(direction === 'next' && index >= self.stepsAmount-1) self.navButtonNext.addClass('disabled');
        if(direction === 'prev' && index === 0) self.navButtonPrev.addClass('disabled');


        $(self.panelSteps[index]).closest('li').addClass('active');
        $(self.stepContents[index]).addClass('active');
    };

    this.goToStepFromPanel = function (e) {
        e.preventDefault();
        var stepIndex = parseInt($(e.currentTarget).attr('data-step'));

        self.activeStepIndex = stepIndex;

        self.navButtonNext.removeClass('disabled');
        self.navButtonPrev.removeClass('disabled');
        self.disactivateSteps();

        $(self.panelSteps[stepIndex]).closest('li').addClass('active');
        $(self.stepContents[stepIndex]).addClass('active');

        if(stepIndex === 0) self.navButtonPrev.addClass('disabled');
        if(stepIndex === self.stepsAmount-1) self.navButtonNext.addClass('disabled');


    };


    this.navButtonNext.on('click', this.goNextStep);
    this.navButtonPrev.on('click', this.goPrevStep);

    this.panelSteps.on('click', this.goToStepFromPanel)

}

$(function () {
    //show more text
    //check text height on page load
    $('.ldev-question [data-text-more-height]').each(function (i, e) {


        var text_data_height = $(e).attr('data-text-more-height');
        var el_height = $(e).height();


        if(parseInt(el_height) !== parseInt(text_data_height) && parseInt(el_height) > parseInt(text_data_height)){
            $(e).css('height', text_data_height)
        }else{
            $(e).find('.btn-show-more-toggle').hide();
        }

    });


    //listen click
    var btn_text_toggle = $('.ldev-question-text-show-more .btn-show-more-toggle');

    btn_text_toggle.on('click', toggleText);

    function toggleText(e) {
        e.preventDefault();
        var text_container = $(this).closest('.ldev-question-text-show-more');
        if(!text_container.attr('data-text-more-shown') || text_container.attr('data-text-more-shown') !== '1'){
            text_container.attr('data-text-more-basic-height',text_container.height());

            text_container.css('height','auto');
            text_container.attr('data-text-more-shown','1');
        }else{
            var basic_height = text_container.attr('data-text-more-basic-height');
            if(basic_height){
                text_container.css('height',basic_height+'px');
            }
            text_container.attr('data-text-more-shown','0');
        }


    }

});


//bootstrap click fix

$(document).on('click', '.ldev-menu .dropdown-menu', function (e) {
    e.stopPropagation();
});


