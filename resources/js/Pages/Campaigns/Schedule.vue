<script setup lang="ts">
import {Button} from '@/Components/ui/button';
import {cn} from '@/lib/utils';
import {format} from 'date-fns';
import {Popover, PopoverContent, PopoverTrigger} from '@/Components/ui/popover';
import {Calendar} from '@/Components/ui/v-calendar';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {useForm} from '@inertiajs/vue3';
import {CalendarIcon} from "@radix-icons/vue";
import {Label} from "@/Components/ui/label";

// Props
const {campaign} = defineProps<{
  campaign: {
    id: number;
    uuid: string;
    scheduled_at: string | null;
    frequency: string | null;
    send_at: string | null
  };
}>();

// Frequency options
const frequencyOptions = [
  {label: 'Once-off', value: 'once'},
  {label: 'Weekly', value: 'weekly'},
  {label: 'Bi-weekly', value: 'bi-weekly'},
  {label: 'Monthly', value: 'monthly'},
  {label: 'Quarterly', value: 'quarterly'},
  {label: 'Yearly', value: 'yearly'},
];

// Initialize form
const form = useForm({
  scheduled_at: campaign.scheduled_at ? new Date(campaign.scheduled_at) : null,
  frequency: campaign.frequency || 'once',
  send_at: campaign.send_at || '08:00', // Default to 8:00 AM
});

// Submit the form
const onSubmit = () => {
  form.put(route('campaigns.schedule', campaign.uuid), {
    preserveScroll: true,
    onSuccess: () => {
      alert('Campaign scheduled successfully.');
    },

    onError: () => {
      alert('Failed to schedule the campaign.');
    },
  });
};
</script>

<template>
  <GlobalModal v-slot="{ close }">
    <form class="grid gap-6">

      <!-- Date Selector -->
      <div>
        <label class="block text-sm font-medium">Date</label>
        <Calendar id="scheduled_at" v-model="form.scheduled_at"/>
      </div>

      <!-- Send Time Selector -->
      <div>
        <Label for="send_at" class="block text-sm font-medium">Sending Time</Label>
        <Calendar id="send_at" mode="time" v-model="form.send_at"/>
      </div>

      <!-- Frequency Selector -->
      <div>
        <Label for="frequency">Frequency</Label>

        <Select id="frequency">
          <SelectTrigger class="w-full">
            <SelectValue placeholder="Select frequency" />
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
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-2">
        <Button type="button" variant="ghost" @click="close">Cancel</Button>
        <Button type="button" @click="onSubmit">Schedule</Button>
      </div>

    </form>
  </GlobalModal>
</template>
