<script setup lang="ts">
import {Button} from '@/Components/ui/button';
import {cn} from '@/lib/utils';
import {format} from 'date-fns';
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
import {watch} from "vue";
import InputError from "@/Components/InputError.vue";
import {CalendarIcon} from 'lucide-vue-next'
import {
  DateFormatter,
  type DateValue,
  CalendarDate,
  today,
  getLocalTimeZone,
} from '@internationalized/date'

const {campaign} = defineProps<{
  campaign: {
    id: number;
    uuid: string;
    scheduled_at: Date | null;
    frequency: string | null;
    end_date: Date | null
  };
}>();

const addDays = (date, days) => {

  if (date instanceof Date) {
    date = new CalendarDate(date.getFullYear(), date.getMonth(), date.getDay());
  }

  // Convert native Date to CalendarDate
  return date.add({ days: days }); // Add the number of days

};

const addMonths = (date, months) => {

  if (date instanceof Date) {
    date = new CalendarDate(date.getFullYear(), date.getMonth(), date.getDate());
  }

  return date.add({ months: months });

};

const getEndDateBasedOnFrequency = (date, frequency) => {
  date = date instanceof Date ? new CalendarDate(date.getFullYear(),
    date.getMonth(),
    date.getDate()) : date

  switch (frequency) {
    case 'weekly':
      return addDays(date, 7);
    case 'bi-weekly':
      return addDays(date, 14);
    case 'monthly':
      return addMonths(date, 1);
    case 'quarterly':
      return addMonths(date, 3);
    default:
      return date;
  }
};

// Frequency options
const frequencyOptions = [
  {label: 'Weekly', value: 'weekly'},
  {label: 'Bi-weekly', value: 'bi-weekly'},
  {label: 'Monthly', value: 'monthly'},
  {label: 'Quarterly', value: 'quarterly'},
];

const df = new DateFormatter('en-GB', { dateStyle: 'long' });

const form = useForm({
  scheduled_at: !campaign.scheduled_at
    ? today(getLocalTimeZone())
    : new CalendarDate(
      campaign.scheduled_at.getFullYear(),
      campaign.scheduled_at.getMonth(),
      campaign.scheduled_at.getDate()
    ),

  frequency: campaign.frequency ?? 'weekly',

  end_date: campaign.end_date
    ? new CalendarDate(
      campaign.end_date.getFullYear(),
      campaign.end_date.getMonth(),
      campaign.end_date.getDate())
    : getEndDateBasedOnFrequency(
      campaign.scheduled_at ?? today(getLocalTimeZone()),
      campaign.frequency ?? 'weekly'
    ),
});

// Submit the form
const onSubmit = () => {
  form.put(route('campaigns.console', campaign.uuid), {
    preserveScroll: true,
    onSuccess: () => {
      alert('Campaign scheduled successfully.');
    },

    onError: () => {
      alert('Failed to schedule the campaign.');
    },
  });
};

watch(() => form.scheduled_at, () => {
  form.end_date = getEndDateBasedOnFrequency(form.scheduled_at, form.frequency);
});

watch(() => form.frequency, () => {
  form.end_date = getEndDateBasedOnFrequency(form.scheduled_at, form.frequency);
});
</script>

<template>
  <GlobalModal v-slot="{ close }">
    <form class="grid gap-6">

      <!-- Date Selector -->
      <div>
        <label class="block text-sm font-medium">Start Date</label>
        <Calendar id="scheduled_at" v-model="form.scheduled_at"/>

        <InputError :message="form.errors.scheduled_at"/>
      </div>

      <!-- Send Time Selector -->
      <div>
        <label class="block text-sm font-medium">End Date</label>

        <Popover>
          <PopoverTrigger as-child>
            <Button
              variant="outline"
              :class="cn(
                'w-full justify-start text-left font-normal',
                !form.end_date && 'text-muted-foreground',
              )"
            >
              <CalendarIcon class="mr-2 h-5 w-5"/>
              {{ form.end_date ? df.format(form.end_date.toDate(getLocalTimeZone())) : "Pick an end date" }}
            </Button>
          </PopoverTrigger>

          <PopoverContent class="w-auto p-0">
            <Calendar id="scheduled_at" v-model="form.end_date"/>
          </PopoverContent>
        </Popover>

        <InputError :message="form.errors.end_date"/>
      </div>

      <!-- Frequency Selector -->
      <div>
        <Label for="frequency">Frequency</Label>

        <Select id="frequency" v-model="form.frequency" :disabled="true">

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
