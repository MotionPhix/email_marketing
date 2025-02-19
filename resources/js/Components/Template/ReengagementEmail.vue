<script setup lang="ts">
import { ref } from 'vue'
import type { EmailTemplate } from '@/types'

const template: EmailTemplate = {
  name: 'Re-engagement Index',
  subject: '{% if subscriber.first_name %}{{subscriber.first_name}}, we miss you!{% else %}We miss you!{% endif %}',
  previewText: 'Special offer inside - Come back and see what\'s new!',
  content: `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>We Miss You!</title>
    </head>
    <body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0;">
      <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
          <img src="{{company.logo}}" alt="{{company.name}}" style="max-width: 200px; height: auto;">
        </div>

        <!-- Dynamic Content Based on User History -->
        {% if subscriber.last_purchase_date %}
          <h1 style="color: #1a1a1a; margin-bottom: 20px;">
            We Haven't Seen You Since {{subscriber.last_purchase_date | date('F Y')}}
          </h1>

          {% if subscriber.abandoned_cart %}
            <!-- Abandoned Cart Content -->
            <div style="background-color: #f8fafc; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
              <h2 style="color: #1a1a1a; margin-top: 0;">Your Cart is Waiting!</h2>
              <p style="color: #444444; margin-bottom: 15px;">
                We noticed you left some items in your cart. Come back and get them with a special discount:
              </p>
              <div style="text-align: center; margin: 20px 0;">
                <span style="display: inline-block; background-color: #ef4444; color: white; padding: 8px 16px; border-radius: 4px; font-size: 24px; font-weight: bold;">
                  {{subscriber.discount_code}}
                </span>
                <p style="margin-top: 10px; color: #6b7280; font-size: 14px;">
                  Use this code for {{subscriber.discount_percentage}}% off your next purchase
                </p>
              </div>
              <a href="{{urls.recover_cart}}" style="display: block; background-color: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; text-align: center;">
                Complete Your Purchase
              </a>
            </div>
          {% else %}
            <!-- Regular Re-engagement Content -->
            <div style="background-color: #f8fafc; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
              <h2 style="color: #1a1a1a; margin-top: 0;">Come Back and Save!</h2>
              <p style="color: #444444; margin-bottom: 15px;">
                We've missed you! Here's a special offer to welcome you back:
              </p>
              <div style="text-align: center; margin: 20px 0;">
                <span style="display: inline-block; background-color: #ef4444; color: white; padding: 8px 16px; border-radius: 4px; font-size: 24px; font-weight: bold;">
                  {{subscriber.discount_code}}
                </span>
                <p style="margin-top: 10px; color: #6b7280; font-size: 14px;">
                  Use this code for {{subscriber.discount_percentage}}% off your next purchase
                </p>
              </div>
            </div>
          {% endif %}
        {% else %}
          <!-- New Subscriber Who Never Purchased -->
          <h1 style="color: #1a1a1a; margin-bottom: 20px;">
            Ready to Get Started?
          </h1>
          <div style="background-color: #f8fafc; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
            <h2 style="color: #1a1a1a; margin-top: 0;">Special First-Time Offer!</h2>
            <p style="color: #444444; margin-bottom: 15px;">
              Make your first purchase with us and save:
            </p>
            <div style="text-align: center; margin: 20px 0;">
              <span style="display: inline-block; background-color: #ef4444; color: white; padding: 8px 16px; border-radius: 4px; font-size: 24px; font-weight: bold;">
                FIRSTTIME
              </span>
              <p style="margin-top: 10px; color: #6b7280; font-size: 14px;">
                Get 20% off your first purchase
              </p>
            </div>
          </div>
        {% endif %}

        <!-- What's New Section -->
        <div style="margin-bottom: 30px;">
          <h2 style="color: #1a1a1a; margin-bottom: 20px;">What's New</h2>
          <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
            {% for feature in new_features %}
              <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                <h3 style="color: #1a1a1a; margin-top: 0;">{{feature.title}}</h3>
                <p style="color: #444444; margin-bottom: 15px;">
                  {{feature.description}}
                </p>
                <a href="{{feature.url}}" style="color: #3b82f6; text-decoration: none;">
                  Learn more →
                </a>
              </div>
            {% endfor %}
          </div>
        </div>

        <!-- Social Proof -->
        {% if testimonials %}
          <div style="margin-bottom: 30px;">
            <h2 style="color: #1a1a1a; margin-bottom: 20px;">What Others Are Saying</h2>
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
              {% for testimonial in testimonials %}
                <div style="background-color: #f8fafc; border-radius: 8px; padding: 20px;">
                  <p style="color: #444444; margin-bottom: 10px; font-style: italic;">
                    "{{testimonial.content}}"
                  </p>
                  <p style="color: #6b7280; margin: 0; font-size: 14px;">
                    - {{testimonial.author}}
                  </p>
                </div>
              {% endfor %}
            </div>
          </div>
        {% endif %}

        <!-- Personalized Recommendations -->
        {% if subscriber.viewed_products %}
          <div style="margin-bottom: 30px;">
            <h2 style="color: #1a1a1a; margin-bottom: 20px;">Recommended For You</h2>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
              {% for product in recommended_products | limit(4) %}
                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px;">
                  <img src="{{product.image}}" alt="{{product.name}}" style="width: 100%; height: auto; border-radius: 4px;">
                  <h3 style="color: #1a1a1a; margin: 10px 0; font-size: 16px;">{{product.name}}</h3>
                  <p style="color: #6b7280; margin: 0; font-size: 14px;">
                    {% if product.sale_price %}
                      <span style="text-decoration: line-through;">${{product.regular_price}}</span>
                      <span style="color: #ef4444; font-weight: bold;">${{product.sale_price}}</span>
                    {% else %}
                      ${{product.regular_price}}
                    {% endif %}
                  </p>
                  <a href="{{product.url}}" style="display: block; background-color: #3b82f6; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; text-align: center; margin-top: 10px;">
                    View Product
                  </a>
                </div>
              {% endfor %}
            </div>
          </div>
        {% endif %}

        <!-- Footer -->
        <div style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 30px; text-align: center; color: #6b7280; font-size: 14px;">
          <p style="margin-bottom: 10px;">
            You're receiving this email because you subscribed to updates from {{company.name}}.<br>
            Last login: {{subscriber.last_login | date('F j, Y')}}
          </p>
          <p style="margin-bottom: 10px;">
            <a href="{{urls.preferences}}" style="color: #6b7280; text-decoration: none;">Email Preferences</a> |
            <a href="{{urls.unsubscribe}}" style="color: #6b7280; text-decoration: none;">Unsubscribe</a>
          </p>
          <p style="margin: 0;">
            {{company.name}}<br>
            {{company.address}}
          </p>
        </div>
      </div>
    </body>
    </html>
  \`
}
</script>
