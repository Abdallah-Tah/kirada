<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;

class OnboardingGuide extends Component
{
    public function __construct(public User $user) {}

    /**
     * @return array<int, array{title: string, description: string, path: string}>
     */
    private function steps(): array
    {
        if ($this->user->isTenant()) {
            return [
                [
                    'title' => __('Review your lease and rent'),
                    'description' => __('Your dashboard keeps the active lease, next due date, invoices, and payment history together so you always know what is due.'),
                    'path' => __('Start with My Rent in the sidebar.'),
                ],
                [
                    'title' => __('Pay outside Kirada, then upload proof'),
                    'description' => __('Use the payment account instructions from your landlord, then submit the receipt in Kirada. The landlord confirms the payment after reviewing the proof.'),
                    'path' => __('Open an unpaid invoice and choose Submit payment proof.'),
                ],
                [
                    'title' => __('Report maintenance clearly'),
                    'description' => __('Send a description, urgency, preferred access time, and photos. Follow the request status and messages from one place.'),
                    'path' => __('Open Maintenance to create or track a request.'),
                ],
                [
                    'title' => __('Keep communication in Kirada'),
                    'description' => __('Messages and tenant-visible documents stay attached to your rental history, making questions and records easy to find.'),
                    'path' => __('Use Messages and Documents in the sidebar.'),
                ],
                [
                    'title' => __('Protect your account'),
                    'description' => __('Set up a passkey and TOTP two-factor authentication, then save recovery codes in a password manager.'),
                    'path' => __('Open Settings → Security when you are ready.'),
                ],
            ];
        }

        if ($this->user->isMaintenance()) {
            return [
                [
                    'title' => __('Publish your provider profile'),
                    'description' => __('Add your trades, service area, pricing approach, availability, and portfolio. A published profile is what landlords can discover.'),
                    'path' => __('Open My Profile and publish it when it is complete.'),
                ],
                [
                    'title' => __('Build trusted landlord connections'),
                    'description' => __('Review landlord invitations in your maintenance network and accept only the connections you are ready to serve.'),
                    'path' => __('Open Invitations in the maintenance workspace.'),
                ],
                [
                    'title' => __('Manage work orders end to end'),
                    'description' => __('Review photos and priority, ask questions, submit quotes, keep statuses current, and attach completion notes and photos.'),
                    'path' => __('Open Assigned Requests to manage your work.'),
                ],
                [
                    'title' => __('Build your reputation'),
                    'description' => __('Completed work can receive a verified review from the landlord. Keep quotes, invoices, and completion evidence attached to the request.'),
                    'path' => __('Use Messages for coordination and the request timeline for evidence.'),
                ],
                [
                    'title' => __('Secure your workspace'),
                    'description' => __('Add a passkey and enable two-factor authentication before accepting sensitive work or sharing account access.'),
                    'path' => __('Open Settings → Security.'),
                ],
            ];
        }

        if ($this->user->isAdmin()) {
            return [
                [
                    'title' => __('Monitor the platform'),
                    'description' => __('Use the administration dashboard to review adoption, portfolios, subscriptions, leases, payments, and maintenance activity.'),
                    'path' => __('Start with the platform dashboard.'),
                ],
                [
                    'title' => __('Keep access intentional'),
                    'description' => __('Use roles and permissions to give each account only the access required for its work. Do not share administrator credentials.'),
                    'path' => __('Review the Property Team and account settings areas.'),
                ],
                [
                    'title' => __('Use the audit trail'),
                    'description' => __('The Audit Center helps you investigate sensitive changes without exposing private credentials or payment secrets.'),
                    'path' => __('Open Audit Center for security and operational review.'),
                ],
                [
                    'title' => __('Verify notifications and recovery'),
                    'description' => __('Keep BWA delivery, queue workers, 2FA, and passkeys tested before enabling production workflows.'),
                    'path' => __('Use Settings → Security and notification settings.'),
                ],
            ];
        }

        return [
            [
                'title' => __('Set up your portfolio'),
                'description' => __('Add properties, buildings, and units first. Kirada uses this structure to connect leases, tenants, invoices, maintenance, and reports.'),
                'path' => __('Start with Properties, then add Units.'),
            ],
            [
                'title' => __('Add payment instructions'),
                'description' => __('Publish the D-Money, Waafi, CAC Bank, bank transfer, or cash accounts your tenants may use. Kirada does not hold rent money in the first release.'),
                'path' => __('Open Settings → Payment accounts and mark one account as primary.'),
            ],
            [
                'title' => __('Onboard tenants and create leases'),
                'description' => __('Create tenant records, invite tenants by email or WhatsApp, then create a lease with rent, deposit, due date, and reminder settings.'),
                'path' => __('Use Tenants, Invitations, and Leases in that order.'),
            ],
            [
                'title' => __('Run rent operations'),
                'description' => __('Create invoices, review uploaded payment proof, confirm payments, and use delivery history to diagnose email or WhatsApp notifications.'),
                'path' => __('Open Rent Invoices and Rent Payments each collection cycle.'),
            ],
            [
                'title' => __('Build your team and maintenance network'),
                'description' => __('Invite staff with the least-privileged role, publish maintenance requests, and assign approved providers. Keep reviews and completion evidence attached to each job.'),
                'path' => __('Use Property Team, Maintenance, and Find Maintenance.'),
            ],
        ];
    }

    private function roleLabel(): string
    {
        return match (true) {
            $this->user->isTenant() => __('Tenant workspace'),
            $this->user->isMaintenance() => __('Maintenance workspace'),
            $this->user->isAdmin() => __('Administrator workspace'),
            default => __('Landlord workspace'),
        };
    }

    public function render(): View
    {
        return view('components.onboarding-guide', [
            'steps' => $this->steps(),
            'roleLabel' => $this->roleLabel(),
        ]);
    }
}
