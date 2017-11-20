
(function($) {
    cycles = {

        available_cycles: {},
        cycle_list:null,
        campaign_list:null,
        cycle_checkbox:null,
        cycle_label:null,
        selected_cycle_day:null,

        init:function(available_cycles, campaign_list, cycle_list, cycle_checkbox, cycle_label, selected_cycle_day) {
            this.available_cycles = available_cycles;
            this.cycle_list = cycle_list;
            this.campaign_list = campaign_list;
            this.cycle_checkbox = cycle_checkbox;
            this.cycle_label = cycle_label;

            this.set_initial_cycle_day(selected_cycle_day);

            this.campaign_list.on('change', function() {

                campaign_id = campaign_list.val();
                campaign_cycles = this.available_cycles[campaign_id];

                if (!campaign_cycles) {
                    this.turn_off_cycles();
                    return;
                }

                this.turn_on_cycles(campaign_cycles);

            }.bind(this));

            this.campaign_list.trigger('change');

        },

        set_initial_cycle_day: function(day) {

            if (day !== parseInt(day) || day === null) {
                return;
            }

            this.selected_cycle_day = day;
            this.cycle_checkbox.attr('checked', 'checked');
        },

        turn_on_cycles: function(campaign_cycles) {
            this.cycle_list.html('');

            $.each(campaign_cycles, function (index, cycle) {

                selected = this.selected_cycle_day == cycle.day ? 'selected' : '';
                this.cycle_list.append(
                    '<option value="' + cycle.day + '" ' + selected + ' >Day ' + cycle.day + ':' + cycle.name + ' (' + cycle.status + ')</option>'
                );
            }.bind(this));

            this.cycle_list.removeClass('inactive');
            this.cycle_list.removeAttr('disabled');
            this.cycle_checkbox.removeAttr('disabled');
            this.cycle_label.removeClass('inactive');
        },

        turn_off_cycles: function() {
            this.cycle_list.html('<option value="">no autoresponders</option>');
            this.cycle_list.addClass('inactive');
            this.cycle_list.attr('disabled', 'disabled');

            this.cycle_checkbox.attr('disabled', 'disabled');
            this.cycle_label.addClass('inactive');
        }

    };
})(jQuery);