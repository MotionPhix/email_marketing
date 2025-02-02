<script setup lang="ts">
import {ref, computed} from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import {Button} from "@/Components/ui/button";
import {Link} from "@inertiajs/vue3";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/Components/ui/card";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/Components/ui/dialog";
import {Badge} from "@/Components/ui/badge";
import {Tabs, TabsContent, TabsList, TabsTrigger} from "@/Components/ui/tabs";
import {ScrollArea} from "@/Components/ui/scroll-area";
import {
  Users,
  Mail,
  Calendar,
  ChevronRight,
  Trash2,
  Edit,
  UserPlus,
  MailPlus,
} from 'lucide-vue-next';
import {useTabPersistence} from "@/composables/useTabPersistence";

interface Recipient {
  uuid: string;
  name: string;
  email: string;
  created_at: string;
}

interface Campaign {
  uuid: string;
  title: string;
  subject: string;
  status: string;
  created_at: string;
}

interface Audience {
  uuid: string;
  name: string;
  description: string;
  created_at: string;
  recipients: Recipient[];
  campaigns: Campaign[];
}

const props = defineProps<{
  audience: Audience;
}>();

const {activeTab, handleTabChange} = useTabPersistence()
const showDeleteDialog = ref(false);
const recipientToRemove = ref<string | null>(null);
const campaignToDelete = ref<string | null>(null);

const recipientCount = computed(() => props.audience.recipients.length);
const campaignCount = computed(() => props.audience.campaigns.length);

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const confirmDelete = (uuid: string) => {
  showDeleteDialog.value = true;
};

const confirmRemoveRecipient = (uuid: string) => {
  recipientToRemove.value = uuid;
};

const confirmDeleteCampaign = (uuid: string) => {
  campaignToDelete.value = uuid;
};
</script>

<template>
  <AppLayout :title="audience.name">
    <template #header>
      <div class="flex items-center space-x-4">
        <div class="flex flex-col">
          <div class="flex items-center space-x-2">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
              {{ audience.name }}
            </h2>
            <Badge variant="secondary" class="text-sm">
              {{ recipientCount }} Recipients
            </Badge>
          </div>
          <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <Calendar class="h-4 w-4"/>
            <span>Created {{ formatDate(audience.created_at) }}</span>
          </div>
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <GlobalLink
          variant="outline" as="Button"
          :href="route('audiences.edit', audience.uuid)">
          <Edit class="h-4 w-4 mr-2"/>
          Edit Audience
        </GlobalLink>

        <Button variant="destructive" @click="confirmDelete">
          <Trash2 class="h-4 w-4 mr-2"/>
          Delete
        </Button>
      </div>
    </template>

    <div class="py-12">
      <Tabs v-model="activeTab" class="space-y-6" @change="handleTabChange">
        <TabsList class="bg-white dark:bg-gray-800 p-1 rounded-lg">
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="recipients">Recipients</TabsTrigger>
          <TabsTrigger value="campaigns">Campaigns</TabsTrigger>
        </TabsList>

        <TabsContent value="overview">
          <Card>
            <CardHeader>
              <CardTitle>Audience Overview</CardTitle>
              <CardDescription>
                {{ audience.description || 'No description provided' }}
              </CardDescription>
            </CardHeader>

            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="flex items-center space-x-4">
                      <Users class="h-8 w-8 text-primary"/>
                      <div>
                        <p class="text-sm font-medium">Total Recipients</p>
                        <p class="text-2xl font-bold">{{ recipientCount }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <div class="flex items-center space-x-4">
                      <Mail class="h-8 w-8 text-primary"/>
                      <div>
                        <p class="text-sm font-medium">Total Campaigns</p>
                        <p class="text-2xl font-bold">{{ campaignCount }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="recipients">
          <Card>
            <CardHeader>
              <div class="flex items-center justify-between">
                <CardTitle>Recipients</CardTitle>
                <Button as-child>
                  <Link :href="route('audiences.add_recipient', audience.uuid)">
                    <UserPlus class="h-4 w-4 mr-2"/>
                    Add Recipients
                  </Link>
                </Button>
              </div>
            </CardHeader>

            <CardContent>
              <ScrollArea class="h-[400px]">
                <div class="space-y-4">
                  <div
                    v-for="recipient in audience.recipients"
                    :key="recipient.uuid"
                    class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                  >
                    <div class="flex items-center space-x-4">
                      <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="text-primary font-medium">
                          {{ recipient.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>

                      <div>
                        <p class="font-medium">{{ recipient.name }}</p>
                        <p class="text-sm text-gray-500">{{ recipient.email }}</p>
                      </div>
                    </div>

                    <div class="flex items-center space-x-2">
                      <GlobalLink
                        as="Button"
                        variant="outline"
                        size="icon"
                        :href="route('recipients.edit', recipient.uuid)">
                        <Edit class="h-4 w-4"/>
                      </GlobalLink>

                      <Button
                        size="icon"
                        variant="destructive"
                        @click="() => confirmRemoveRecipient(recipient.uuid)">
                        <Trash2 class="h-4 w-4"/>
                      </Button>
                    </div>
                  </div>
                </div>
              </ScrollArea>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="campaigns">
          <Card>
            <CardHeader>
              <div class="flex items-center justify-between">
                <CardTitle>Campaigns</CardTitle>
                <Button as-child>
                  <Link :href="route('campaigns.create', audience.uuid)">
                    <MailPlus class="h-4 w-4 mr-2"/>
                    Create Campaign
                  </Link>
                </Button>
              </div>
            </CardHeader>

            <CardContent>
              <ScrollArea class="h-[400px]">
                <div class="space-y-4">
                  <div
                    v-for="campaign in audience.campaigns"
                    :key="campaign.uuid"
                    class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                  >
                    <div>
                      <div class="flex items-center space-x-2">
                        <h4 class="font-medium">{{ campaign.title }}</h4>
                        <Badge>{{ campaign.status }}</Badge>
                      </div>
                      <p class="text-sm text-gray-500">{{ campaign.subject }}</p>
                    </div>

                    <div class="flex items-center space-x-2">
                      <Button
                        variant="outline"
                        size="sm"
                        as-child>
                        <Link :href="route('campaigns.show', campaign.uuid)">
                          View Details
                          <ChevronRight class="h-4 w-4 ml-2"/>
                        </Link>
                      </Button>

                      <Button
                        variant="destructive"
                        size="sm"
                        @click="() => confirmDeleteCampaign(campaign.uuid)">
                        <Trash2 class="h-4 w-4"/>
                      </Button>
                    </div>
                  </div>
                </div>
              </ScrollArea>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>

    <!-- Delete Audience Dialog -->
    <Dialog v-model:open="showDeleteDialog">
      <DialogContent class="max-w-sm">
        <DialogHeader>
          <DialogTitle>Delete Audience</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete this audience? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="showDeleteDialog = false">
            Cancel
          </Button>
          <Button variant="destructive">
            Delete Audience
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
