<script setup lang="ts">
import { ref } from 'vue'
import {CheckIcon, CopyIcon, CheckCircleIcon, ArrowLeftIcon, ArrowRightIcon} from "lucide-vue-next";

const emit = defineEmits(['next', 'back'])
const domain = ref('')
const verificationStatus = ref<'pending' | 'verified' | 'failed'>('pending')
const dnsRecords = ref([
  {
    type: 'TXT',
    host: '_sendgrid.yourdomain.com',
    value: 'k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC...',
    status: 'pending'
  },
  {
    type: 'CNAME',
    host: 'email.yourdomain.com',
    value: 'u27386.wl123.sendgrid.net',
    status: 'pending'
  },
  {
    type: 'TXT',
    host: 'yourdomain.com',
    value: 'v=spf1 include:sendgrid.net ~all',
    status: 'pending'
  }
])

const verifyDomain = async () => {
  if (!domain.value) return

  // Update DNS records with actual domain
  dnsRecords.value = dnsRecords.value.map(record => ({
    ...record,
    host: record.host.replace('yourdomain.com', domain.value)
  }))

  // Simulate verification process
  const interval = setInterval(() => {
    const allVerified = dnsRecords.value.every(record => record.status === 'verified')
    if (allVerified) {
      verificationStatus.value = 'verified'
      clearInterval(interval)
    } else {
      dnsRecords.value = dnsRecords.value.map(record => ({
        ...record,
        status: Math.random() > 0.5 ? 'verified' : 'pending'
      }))
    }
  }, 2000)
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Set Up Sending Domain</CardTitle>
      <CardDescription>
        Add and verify your domain to improve email deliverability
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-6">
      <FormField>
        <Label>Domain Name</Label>
        <div class="flex gap-2">
          <Input
            v-model="domain"
            type="text"
            placeholder="yourdomain.com"
            :disabled="verificationStatus === 'verified'"
          />
          <Button
            variant="secondary"
            @click="verifyDomain"
            :disabled="!domain || verificationStatus === 'verified'"
          >
            Verify
          </Button>
        </div>
        <p class="text-xs text-muted-foreground mt-1">
          Enter the domain you want to send emails from
        </p>
      </FormField>

      <div class="space-y-4">
        <h4 class="text-sm font-medium">DNS Records</h4>
        <div class="space-y-4">
          <Card
            v-for="record in dnsRecords"
            :key="record.type + record.host"
            class="relative"
          >
            <div
              v-if="record.status === 'verified'"
              class="absolute right-2 top-2"
            >
              <Badge variant="success" class="gap-1">
                <CheckIcon class="h-3 w-3" />
                Verified
              </Badge>
            </div>
            <CardContent class="p-4">
              <div class="grid gap-1">
                <div class="text-sm font-medium">
                  {{ record.type }} Record
                </div>
                <div class="grid gap-4 rounded-md bg-muted p-3">
                  <div class="grid grid-cols-3 items-center gap-4">
                    <Label class="text-xs">Host</Label>
                    <div class="col-span-2 truncate font-mono text-xs">
                      {{ record.host }}
                    </div>
                  </div>
                  <div class="grid grid-cols-3 items-center gap-4">
                    <Label class="text-xs">Value</Label>
                    <div class="col-span-2">
                      <div class="flex items-center gap-2">
                        <code class="rounded bg-background px-2 py-1 text-xs">
                          {{ record.value }}
                        </code>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-6 w-6"
                          @click="navigator.clipboard.writeText(record.value)"
                        >
                          <CopyIcon class="h-3 w-3" />
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <Alert v-if="verificationStatus === 'verified'" variant="success">
        <CheckCircleIcon class="h-4 w-4" />
        <AlertTitle>Domain Verified</AlertTitle>
        <AlertDescription>
          Your domain has been successfully verified and is ready to use.
        </AlertDescription>
      </Alert>

      <div class="flex justify-between">
        <Button
          variant="outline"
          @click="$emit('back')">
          <ArrowLeftIcon class="mr-2 h-4 w-4" />
          Back
        </Button>

        <Button
          @click="$emit('next')"
          :disabled="verificationStatus !== 'verified'">
          Continue
          <ArrowRightIcon class="ml-2 h-4 w-4" />
        </Button>
      </div>
    </CardContent>
  </Card>
</template>
