<script setup lang="ts">
import {Button} from '@/Components/ui/button';
import {cn} from '@/lib/utils';
import {Popover, PopoverContent, PopoverTrigger} from '@/Components/ui/popover'
import {Calendar} from '@/Components/ui/v-calendar';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {useForm} from '@inertiajs/vue3';
import {Label} from "@/Components/ui/label";
import {ref, watch} from "vue";
import InputError from "@/Components/InputError.vue";
import {CalendarIcon} from 'lucide-vue-next'
import {
  DateFormatter,
  CalendarDate,
  today,
  getLocalTimeZone,
} from '@internationalized/date'
import {Checkbox} from "@/Components/ui/checkbox";

const {campaign} = defineProps<{
  campaign: {
    id: number;
    uuid: string;
    scheduled_at: Date | null;
    frequency: string | null;
    end_date: Date | null
  };
}>();

const modalRef = ref()

const disabledDates = ref([
  {
    start: null,
    end: today(getLocalTimeZone())
  }
]);

const addWeeks = (date, weeks) => {

  if (date instanceof Date) {
    date = new CalendarDate(date.getFullYear(), date.getMonth(), date.getDate());
  }

  // Convert native Date to CalendarDate
  return date.add({ weeks: weeks }); // Add the number of days

};

const addMonths = (date, months) => {

  if (date instanceof Date) {
    date = new CalendarDate(date.getFullYear(), date.getMonth(), date.getDate());
  }

  return date.add({months: months});

};

const getEndDateBasedOnFrequency = (date, frequency) => {
  const calendarDate = date instanceof Date
    ? new CalendarDate(
      date.getFullYear(),
      date.getMonth(),
      date.getDate())
    : date

  switch (frequency) {
    case 'weekly':
      return addWeeks(calendarDate, 1);
    case 'bi_weekly':
      return addWeeks(calendarDate, 2);
    case 'monthly':
      return addMonths(calendarDate, 1);
    case 'quarterly':
      return addMonths(calendarDate, 3);
    default:
      return calendarDate;
  }
};

// Frequency options
const frequencyOptions = [
  {label: 'Weekly', value: 'weekly'},
  {label: 'Bi-weekly', value: 'bi_weekly'},
  {label: 'Monthly', value: 'monthly'},
  {label: 'Quarterly', value: 'quarterly'},
];

const df = new DateFormatter('en-ZA', {
  dateStyle: 'long'
});

const form = useForm({
  scheduled_at: !campaign.scheduled_at
    ? today(getLocalTimeZone())
    : new CalendarDate(
      campaign.scheduled_at.getFullYear(),
      campaign.scheduled_at.getMonth() + 1,
      campaign.scheduled_at.getDate()
    ),

  frequency: campaign.frequency ?? 'weekly',

  end_date: campaign.end_date
    ? new CalendarDate(
      campaign.end_date.getFullYear(),
      campaign.end_date.getMonth() + 1,
      campaign.end_date.getDate())
    : getEndDateBasedOnFrequency(
      campaign.scheduled_at ?? today(getLocalTimeZone()),
      campaign.frequency ?? 'weekly'
    ),

  stop_campaign: false,
});

// Submit the form
const onSubmit = () => {
  form.put(route('campaigns.console', campaign.uuid), {
    preserveScroll: true,
    onSuccess: () => {
      modalRef.value.close()
    },

    onError: () => {
      alert('Failed to schedule the campaign.');
    },
  });
};

watch(() => form.scheduled_at, (newScheduledAt) => {
  if (!(newScheduledAt instanceof CalendarDate)) {
    form.scheduled_at = new CalendarDate(
      newScheduledAt.year || newScheduledAt.getFullYear(),
      newScheduledAt.month || newScheduledAt.getMonth() + 1,
      newScheduledAt.day || newScheduledAt.getDate()
    );
  }

  form.end_date = getEndDateBasedOnFrequency(
    form.scheduled_at,
    form.frequency
  );
});

watch(() => form.frequency, (newFrequency) => {
  form.end_date = getEndDateBasedOnFrequency(
    form.scheduled_at,
    newFrequency
  );
});
</script>

<template>
  <GlobalModal v-slot="{ close }" ref="modalRef">
    <form class="grid gap-6">

      <!-- Date Selector -->
      <div>
        <label for="scheduled_at" class="block text-sm font-medium">Start Date</label>

        <Calendar
          id="scheduled_at"
          :disabled-dates="disabledDates"
          v-model="form.scheduled_at" />

        <InputError :message="form.errors.scheduled_at"/>
      </div>

      <!-- Send Time Selector -->
      <div>
        <div class="flex items-center justify-between py-2">

          <label for="end_date" class="block text-sm font-medium">
            End Date
          </label>

          <div class="flex items-center space-x-2">
            <Checkbox
              :disabled="! campaign.scheduled_at"
              id="stop_campaign" v-model="form.stop_campaign"/>

            <Label
              for="stop_campaign"
              class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
              Stop Campaign
            </Label>

          </div>

        </div>

        <Popover>
          <PopoverTrigger as-child>
            <Button
              :disabled="true"
              variant="outline"
              :class="cn(
                'w-full justify-start text-left font-normal',
                !form.end_date && 'text-muted-foreground',
              )"
            >
              <CalendarIcon class="mr-2 h-4 w-4"/>
              {{ form.end_date ? df.format(form.end_date.toDate(getLocalTimeZone())) : "Pick an end date" }}
            </Button>
          </PopoverTrigger>

          <PopoverContent class="w-auto p-0">
            <Calendar id="end_date" v-model="form.end_date"/>
          </PopoverContent>
        </Popover>

        <InputError :message="form.errors.end_date"/>
      </div>

      <!-- Frequency Selector -->
      <div>
        <Label for="frequency">Frequency</Label>

        <Select id="frequency" v-model="form.frequency">

          <SelectTrigger class="w-full">
            <SelectValue placeholder="Select frequency"/>
          </SelectTrigger>

          <SelectContent>

            <SelectItem
              :key="option.value"
              v-for="option in frequencyOptions"
              :value="option.value">
              {{ option.label }}
            </SelectItem>

          </SelectContent>
        </Select>

        <InputError :message="form.errors.frequency"/>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-2">
        <Button type="button" variant="ghost" @click="close">Cancel</Button>
        <Button type="button" @click="onSubmit">Schedule</Button>
      </div>

    </form>
  </GlobalModal>
</template>
