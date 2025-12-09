<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Development seeder that creates a complete testing environment
 * with a user, mailbox configured for Greenmail, and sample campaign.
 */
class DevSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->command->info('Created admin user: admin@example.com / password');

        // Create dev user (standard user)
        $user = User::factory()->create([
            'name' => 'Dev User',
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_USER,
        ]);

        $this->command->info('Created user: dev@example.com / password');

        // Create Greenmail-configured mailbox
        $mailbox = Mailbox::factory()->greenmail()->create([
            'user_id' => $user->id,
            'name' => 'Test Mailbox',
            'status' => Mailbox::STATUS_ACTIVE,
            'daily_limit' => 100,
        ]);

        $this->command->info("Created mailbox: {$mailbox->email_address}");

        // Create a sample campaign
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'mailbox_id' => $mailbox->id,
            'name' => 'Welcome Campaign',
            'status' => Campaign::STATUS_ACTIVE,
        ]);

        $this->command->info("Created campaign: {$campaign->name}");

        // Create email templates for the sequence
        EmailTemplate::factory()->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'name' => 'Initial Outreach',
            'subject' => 'Hi {{first_name}}, quick question about {{company}}',
            'body' => '<p>Hi {{first_name}},</p>
<p>I noticed you work at {{company}} and wanted to reach out.</p>
<p>Would you have 15 minutes this week for a quick chat?</p>
<p>Best,<br>Dev User</p>',
            'sequence_order' => 1,
            'delay_days' => 0,
        ]);

        EmailTemplate::factory()->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'name' => 'Follow-up',
            'subject' => 'Re: Hi {{first_name}}, quick question about {{company}}',
            'body' => '<p>Hi {{first_name}},</p>
<p>Just following up on my previous email. I\'d love to connect.</p>
<p>Best,<br>Dev User</p>',
            'sequence_order' => 2,
            'delay_days' => 3,
        ]);

        $this->command->info('Created 2 email templates');

        // Create sample leads
        $leads = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@localhost', 'company' => 'Acme Inc', 'role' => 'CEO'],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'email' => 'jane@localhost', 'company' => 'TechCorp', 'role' => 'CTO'],
            ['first_name' => 'Bob', 'last_name' => 'Johnson', 'email' => 'bob@localhost', 'company' => 'StartupXYZ', 'role' => 'Founder'],
        ];

        foreach ($leads as $leadData) {
            Lead::factory()->create([
                'campaign_id' => $campaign->id,
                'status' => Lead::STATUS_PENDING,
                ...$leadData,
            ]);
        }

        $this->command->info('Created 3 sample leads');

        $this->command->newLine();
        $this->command->info('Dev environment ready!');
        $this->command->info('Admin Login: admin@example.com / password');
        $this->command->info('User Login: dev@example.com / password');
        $this->command->info('Dev Mail Tool: /dev/mail');
    }
}
