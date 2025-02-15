<script setup lang="ts">
import { ref } from 'vue'
import type { EmailTemplate } from '@/types'

const props = defineProps<{
  templateId?: string
}>()

const template: EmailTemplate = {
  name: 'Welcome Series - Day 1',
  subject: 'Welcome to {{company.name}} - Let\'s get started!',
  previewText: 'Welcome aboard! Here\'s everything you need to know to get started.',
  content: `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Welcome to {{company.name}}</title>
    </head>
    <body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0;">
      <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
          <img src="{{company.logo}}" alt="{{company.name}}" style="max-width: 200px; height: auto;">
        </div>

        <!-- Personalized Greeting -->
        {% if subscriber.first_name %}
          <h1 style="color: #1a1a1a; margin-bottom: 20px;">Welcome, {{subscriber.first_name}}! 👋</h1>
        {% else %}
          <h1 style="color: #1a1a1a; margin-bottom: 20px;">Welcome to {{company.name}}! 👋</h1>
        {% endif %}

        <p style="color: #444444; margin-bottom: 20px;">
          We're thrilled to have you on board! Let's get you started with everything you need to know.
        </p>

        <!-- Account Type Specific Content -->
        {% if subscriber.account_type == 'trial' %}
          <div style="background-color: #f8fafc; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
            <h2 style="color: #1a1a1a; margin-top: 0;">Your Free Trial</h2>
            <p style="color: #444444; margin-bottom: 15px;">
              You have {{subscriber.trial_days_left}} days left in your trial. Here's what you can do:
            </p>
            <ul style="color: #444444; padding-left: 20px;">
              <li>Explore all premium features</li>
              <li>Set up your first project</li>
              <li>Invite team members</li>
            </ul>
            <a href="{{urls.upgrade}}" style="display: inline-block; background-color: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 15px;">
              Upgrade Now
            </a>
          </div>
        {% endif %}

        <!-- Getting Started Section -->
        <h2 style="color: #1a1a1a; margin-bottom: 20px;">Quick Start Guide</h2>

        <div style="margin-bottom: 30px;">
          {% if subscriber.onboarding_progress == 0 %}
            <!-- New User Content -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
              <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                <h3 style="color: #1a1a1a; margin-top: 0;">1. Complete Your Profile</h3>
                <p style="color: #444444; margin-bottom: 15px;">
                  Take a moment to set up your profile and preferences.
                </p>
                <a href="{{urls.profile_setup}}" style="color: #3b82f6; text-decoration: none;">
                  Set up profile →
                </a>
              </div>

              <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                <h3 style="color: #1a1a1a; margin-top: 0;">2. Watch the Tutorial</h3>
                <p style="color: #444444; margin-bottom: 15px;">
                  Learn the basics in our 5-minute video tutorial.
                </p>
                <a href="{{urls.tutorial}}" style="color: #3b82f6; text-decoration: none;">
                  Watch now →
                </a>
              </div>

              <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                <h3 style="color: #1a1a1a; margin-top: 0;">3. Create Your First Project</h3>
                <p style="color: #444444; margin-bottom: 15px;">
                  Start with a template or create from scratch.
                </p>
                <a href="{{urls.create_project}}" style="color: #3b82f6; text-decoration: none;">
                  Create project →
                </a>
              </div>
            </div>
          {% else %}
            <!-- Partially Onboarded User Content -->
            <p style="color: #444444; margin-bottom: 20px;">
              Great progress so far! Here's what's next on your journey:
            </p>
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
              {% if subscriber.onboarding_progress < 50 %}
                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                  <h3 style="color: #1a1a1a; margin-top: 0;">Complete Your Setup</h3>
                  <p style="color: #444444; margin-bottom: 15px;">
                    You're {{subscriber.onboarding_progress}}% through the setup process.
                  </p>
                  <a href="{{urls.continue_setup}}" style="color: #3b82f6; text-decoration: none;">
                    Continue setup →
                  </a>
                </div>
              {% endif %}
            </div>
          {% endif %}
        </div>

        <!-- Help & Support -->
        <div style="background-color: #f8fafc; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
          <h2 style="color: #1a1a1a; margin-top: 0;">Need Help?</h2>
          <p style="color: #444444; margin-bottom: 15px;">
            Our support team is here to help you get started:
          </p>
          <ul style="color: #444444; padding-left: 20px; margin-bottom: 15px;">
            <li>Check out our <a href="{{urls.help_center}}" style="color: #3b82f6; text-decoration: none;">Help Center</a></li>
            <li>Join our <a href="{{urls.community}}" style="color: #3b82f6; text-decoration: none;">Community</a></li>
            <li>Contact <a href="{{urls.support}}" style="color: #3b82f6; text-decoration: none;">Support</a></li>
          </ul>
        </div>

        <!-- Footer -->
        <div style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 30px; text-align: center; color: #6b7280; font-size: 14px;">
          <p style="margin-bottom: 10px;">
            {{company.name}}<br>
            {{company.address}}
          </p>
          <p style="margin-bottom: 10px;">
            <a href="{{urls.preferences}}" style="color: #6b7280; text-decoration: none;">Email Preferences</a> |
            <a href="{{urls.unsubscribe}}" style="color: #6b7280; text-decoration: none;">Unsubscribe</a>
          </p>
        </div>
      </div>
    </body>
    </html>
  `
}
</script>
