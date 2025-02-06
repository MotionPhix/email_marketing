<script setup lang="ts">
import {Link} from '@inertiajs/vue3'
import {useDark, useToggle} from '@vueuse/core'
import {
  IconMail,
  IconChartLine,
  IconUsers,
  IconRocket,
  IconMoon,
  IconSun,
  IconArrowRight,
  IconPlayerPlay,
  IconDotsVertical,
  IconBrandGithub,
  IconBrandTwitter
} from '@tabler/icons-vue'

import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/Components/ui/accordion"
import ApplicationMark from "@/Components/ApplicationMark.vue"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger
} from "@/Components/ui/dropdown-menu";
import PricingCard from "@/Pages/Home/Components/PricingCard.vue";

const isDark = useDark()
const toggleDark = useToggle(isDark)

// Props for the pricing plans
interface Feature {
  uuid: string
  analytics: string
  campaign_limit: string
  can_schedule_campaigns: string
  email_limit: string
  personalisation: string
  recipient_limit: string
  segment_limit: string
  support_type: string
}

interface Plan {
  uuid: string
  name: string
  price: number
  features: Feature[]
}

interface Props {
  plans: Plan[]
  currentPlan?: Plan | null
}

const props = defineProps<Props>()

const features = [
  {
    icon: IconMail,
    title: "Smart Campaign Management",
    description: "Create and automate email campaigns with our intuitive drag-and-drop interface."
  },
  {
    icon: IconChartLine,
    title: "Advanced Analytics",
    description: "Get detailed insights into your campaign performance with real-time tracking and reporting."
  },
  {
    icon: IconUsers,
    title: "Audience Segmentation",
    description: "Target the right audience with powerful segmentation and personalization tools."
  },
  {
    icon: IconRocket,
    title: "Automation Tools",
    description: "Save time with automated workflows, triggers, and scheduled campaigns."
  }
]

const faqs = [
  {
    question: "How does the free trial work?",
    answer: "Our 14-day free trial gives you full access to all features. No credit card required."
  },
  {
    question: "Can I import my existing email list?",
    answer: "Yes, you can easily import contacts from CSV files or integrate with your existing tools."
  },
  {
    question: "What kind of support do you offer?",
    answer: "We offer 24/7 email support for all plans and priority phone support for premium plans."
  },
  {
    question: "Is there a limit on emails sent?",
    answer: "Limits vary by plan. Our enterprise plan offers unlimited sends."
  }
]

// Helper function to format features for display
const formatFeatureList = (features: Feature) => {
  return [
    { text: features?.campaign_limit, icon: true },
    { text: features?.recipient_limit, icon: true },
    { text: features?.email_limit, icon: true },
    { text: features?.support_type, icon: true },
    { text: features?.can_schedule_campaigns ? 'Scheduled campaigns' : 'No campaign scheduling', icon: features?.can_schedule_campaigns },
    { text: features?.personalisation ? 'Custom branding & personalization' : 'Basic personalization', icon: features?.personalisation },
    { text: features?.analytics, icon: true },
  ]
}
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-gray-900">
    <!-- Header -->
    <header class="fixed w-full top-0 z-50 border-b bg-background/80 backdrop-blur-sm">
      <nav class="container flex items-center justify-between h-16">
        <Link :href="route('home')" class="flex items-center space-x-2">
          <ApplicationMark class="h-8 w-8"/>
          <span class="font-headings text-xl">{{ $page.props.appName }}</span>
        </Link>

        <div class="flex items-center space-x-4">
          <Button
            variant="ghost"
            size="icon"
            @click="toggleDark()">
            <IconMoon v-if="isDark" class="h-5 w-5"/>
            <IconSun v-else class="h-5 w-5"/>
          </Button>

          <Button
            as-child
            class="hidden sm:inline-block"
            variant="ghost">
            <Link as="button" :href="route('login')">
              Sign in
            </Link>
          </Button>

          <Button
            as-child
            class="hidden sm:inline-block">
            <Link as="button" :href="route('register')">
              Sign Up
            </Link>
          </Button>

          <DropdownMenu>
            <DropdownMenuTrigger class="inline-block sm:hidden">
              <Button size="icon" variant="outline">
                <IconDotsVertical/>
              </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end">
              <DropdownMenuItem>
                <Link :href="route('login')">
                  Sign in
                </Link>
              </DropdownMenuItem>

              <DropdownMenuItem>
                <Link :href="route('register')">
                  Get Started
                </Link>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </nav>
    </header>

    <!-- Hero Section -->
    <section class="container pt-32 pb-20">
      <div class="max-w-3xl mx-auto text-center">
        <h1 class="font-headings text-4xl sm:text-5xl md:text-6xl mb-6">
          Supercharge Your
          <span class="text-primary">Email Marketing</span>
        </h1>

        <p class="text-lg text-muted-foreground mb-8 max-w-2xl mx-auto">
          Create powerful email campaigns, automate your workflows, and drive results with our all-in-one marketing
          platform.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <Link :href="route('register')">
            <Button size="lg" class="w-full sm:w-auto">
              Start Free Trial
              <IconArrowRight class="ml-2 h-4 w-4"/>
            </Button>
          </Link>
          <Button size="lg" variant="outline" class="w-full sm:w-auto">
            Watch Demo
            <IconPlayerPlay class="ml-2 h-4 w-4"/>
          </Button>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="container py-20 bg-muted/30">
      <div class="max-w-6xl mx-auto">
        <h2 class="font-headings text-3xl text-center mb-12">
          Everything you need to succeed
        </h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <Card v-for="feature in features" :key="feature.title" class="border-0 shadow-none">
            <CardContent class="pt-6">
              <component :is="feature.icon" class="h-12 w-12 text-primary mb-4"/>
              <h3 class="font-headings text-xl mb-2">{{ feature.title }}</h3>
              <p class="text-muted-foreground">{{ feature.description }}</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="container py-20">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="font-headings text-3xl mb-4">
            Simple, transparent pricing
          </h2>
          <p class="text-muted-foreground max-w-2xl mx-auto">
            Choose the perfect plan for your needs. All plans include a 14-day free trial.
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <PricingCard
            v-for="plan in plans"
            :key="plan.uuid"
            :plan="plan"
            :current-plan="currentPlan"
            :features="formatFeatureList(plan.features[0])"
            :highlighted="plan.name === 'Pro'"
          />
        </div>

        <div class="mt-12 text-center">
          <p class="text-muted-foreground">
            Need a custom plan?
            <Link
              as="button"
              href="/contact"
              class="text-primary hover:underline">
              Contact us
            </Link>
          </p>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="container py-20 bg-muted/30">
      <div class="max-w-3xl mx-auto">
        <h2 class="font-headings text-3xl text-center mb-12">
          Frequently asked questions
        </h2>
        <Accordion type="single" collapsible>
          <AccordionItem
            v-for="(faq, index) in faqs"
            :key="index"
            :value="`item-${index + 1}`" >
            <AccordionTrigger class="text-left">
              {{ faq.question }}
            </AccordionTrigger>
            <AccordionContent>
              {{ faq.answer }}
            </AccordionContent>
          </AccordionItem>
        </Accordion>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t">
      <div class="container py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
          <div>
            <h3 class="font-headings text-lg mb-4">Product</h3>
            <ul class="space-y-2">
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Features</a></li>
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Pricing</a></li>
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Templates</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-headings text-lg mb-4">Company</h3>
            <ul class="space-y-2">
              <li><a href="#" class="text-muted-foreground hover:text-foreground">About</a></li>
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Blog</a></li>
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Careers</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-headings text-lg mb-4">Resources</h3>
            <ul class="space-y-2">
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Documentation</a></li>
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Help Center</a></li>
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Guides</a></li>
            </ul>
          </div>
          <div>
            <h3 class="font-headings text-lg mb-4">Legal</h3>
            <ul class="space-y-2">
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Privacy</a></li>
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Terms</a></li>
              <li><a href="#" class="text-muted-foreground hover:text-foreground">Security</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t mt-12 pt-8 flex flex-col sm:flex-row justify-between items-center">
          <div class="flex items-center space-x-4 mb-4 sm:mb-0">
            <Link :href="route('home')" class="flex items-center space-x-2">
              <ApplicationMark class="h-8 w-8"/>
              <span class="font-headings text-xl">{{ $page.props.appName }}</span>
            </Link>
          </div>
          <div class="flex items-center space-x-4">
            <Button variant="ghost" size="icon">
              <IconBrandTwitter class="h-5 w-5"/>
            </Button>
            <Button variant="ghost" size="icon">
              <IconBrandGithub class="h-5 w-5"/>
            </Button>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>
