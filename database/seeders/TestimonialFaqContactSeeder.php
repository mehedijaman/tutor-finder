<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialFaqContactSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTestimonials();
        $this->seedFaqs();
        $this->seedContactMessages();
    }

    private function seedTestimonials(): void
    {
        $testimonials = [
            [
                'name' => 'Fatema Rahman',
                'role' => 'Guardian',
                'content' => 'We found an excellent tutor for our daughter through Tutor Finder. The platform made it incredibly easy to connect with qualified tutors in our area. Highly recommended!',
                'rating' => 5,
            ],
            [
                'name' => 'Mahmud Hasan',
                'role' => 'Parent',
                'content' => 'Our son\'s math grades improved significantly after we hired a tutor from here. The verification process gave us peace of mind.',
                'rating' => 5,
            ],
            [
                'name' => 'Nusrat Jahan',
                'role' => 'Guardian',
                'content' => 'Excellent service! The tutors are well-qualified and the booking process is smooth. We are very satisfied with our experience.',
                'rating' => 5,
            ],
            [
                'name' => 'Ahmed Khan',
                'role' => 'Tutor',
                'content' => 'As a tutor, this platform has helped me find consistent students. The payment system is reliable and the support team is very responsive.',
                'rating' => 5,
            ],
            [
                'name' => 'Sadia Islam',
                'role' => 'Parent',
                'content' => 'Very happy with the tutors we have hired. The ability to filter by subject, location, and availability made finding the right match easy.',
                'rating' => 4,
            ],
            [
                'name' => 'Kamal Ahmed',
                'role' => 'Guardian',
                'content' => 'The verification process ensures we get quality tutors. My daughter has improved greatly in her studies since joining.',
                'rating' => 5,
            ],
            [
                'name' => 'Tahmina Begum',
                'role' => 'Parent',
                'content' => 'Great platform for finding home tutors. The tutors are professional and punctual. Highly recommend for anyone looking for quality education.',
                'rating' => 5,
            ],
            [
                'name' => 'Rahman Chowdhury',
                'role' => 'Tutor',
                'content' => 'I have been teaching through Tutor Finder for 2 years now. It is a great platform for both students and tutors to connect.',
                'rating' => 5,
            ],
        ];

        foreach ($testimonials as $index => $data) {
            Testimonial::create([
                'name' => $data['name'],
                'role' => $data['role'],
                'content' => $data['content'],
                'rating' => $data['rating'],
                'status' => 'active',
                'sort_order' => $index + 1,
            ]);
        }

        $this->command->info('Created '.count($testimonials).' testimonials.');
    }

    private function seedFaqs(): void
    {
        $tutorFaqs = [
            ['question' => 'How do I register as a tutor?', 'answer' => 'Click on "Join Now" and select "Tutor" as your role. Fill in your details, verify your phone number, and complete your profile to start receiving tutoring requests.'],
            ['question' => 'Is there any registration fee?', 'answer' => 'No, registration is completely free for tutors. You only pay a small commission when you successfully complete a tutoring job.'],
            ['question' => 'How do I find tuition jobs?', 'answer' => 'Browse the Job Board to find available tuition jobs matching your subjects and location. You can apply directly to jobs that interest you.'],
            ['question' => 'What documents do I need for verification?', 'answer' => 'You will need to provide educational certificates, national ID (NID), and a recent photo. All documents are verified by our team within 48 hours.'],
            ['question' => 'How does payment work?', 'answer' => 'Payments are processed after each tutoring session. You can withdraw your earnings directly to your mobile banking or bank account.'],
            ['question' => 'Can I teach multiple students at once?', 'answer' => 'Yes, you can take group tuitions if you are comfortable with teaching multiple students simultaneously.'],
        ];

        $guardianFaqs = [
            ['question' => 'How do I post a tuition job?', 'answer' => 'Register as a Guardian, click on "Post a Job", fill in the requirements including subject, class, location, and schedule. Qualified tutors will apply to your job.'],
            ['question' => 'How much does it cost to hire a tutor?', 'answer' => 'The tuition fee is negotiated between you and the tutor. Our platform is free for guardians - we only charge tutors a small service fee.'],
            ['question' => 'Can I change the tutor if not satisfied?', 'answer' => 'Yes, you can end the tutoring arrangement at any time and post a new job. We also provide a replacement guarantee within the first week.'],
            ['question' => 'How do I verify a tutor?', 'answer' => 'All tutors on our platform go through a strict verification process including document verification and background checks. Look for the "Verified" badge.'],
            ['question' => 'What areas do you serve?', 'answer' => 'We currently operate in all major cities in Bangladesh including Dhaka, Chattogram, Sylhet, Khulna, and Rajshahi.'],
            ['question' => 'How do I contact support?', 'answer' => 'You can reach our support team through the Contact page, by email at support@tutorfinder.com, or by phone during business hours.'],
        ];

        foreach ($tutorFaqs as $index => $faq) {
            Faq::create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'audience' => 'tutor',
                'status' => 'active',
                'sort_order' => $index + 1,
            ]);
        }

        foreach ($guardianFaqs as $index => $faq) {
            Faq::create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
                'audience' => 'guardian',
                'status' => 'active',
                'sort_order' => $index + 1,
            ]);
        }

        $this->command->info('Created '.count($tutorFaqs) + count($guardianFaqs).' FAQs.');
    }

    private function seedContactMessages(): void
    {
        $messages = [
            [
                'name' => 'Ahmed Hasan',
                'email' => 'ahmed.hasan@example.com',
                'phone' => '+8801711000001',
                'subject' => 'Inquiry about tutoring services',
                'message' => 'Hi, I am interested in hiring a tutor for my son who is in Class 8. He needs help with Mathematics and Physics. Please contact me with more details.',
                'status' => 'open',
            ],
            [
                'name' => 'Fatema Begum',
                'email' => 'fatema.begum@example.com',
                'phone' => '+8801711000002',
                'subject' => 'Become a tutor',
                'message' => 'I am a graduate from BUET and interested in becoming a tutor. I have expertise in Mathematics, Physics, and Chemistry. Please guide me through the registration process.',
                'status' => 'open',
            ],
            [
                'name' => 'Rahman Khan',
                'email' => 'rahman.khan@example.com',
                'phone' => '+8801711000003',
                'subject' => 'Feedback',
                'message' => 'Great platform! I found an excellent tutor for my daughter within 2 days. The verification process gave us confidence in the tutor\'s qualifications.',
                'status' => 'closed',
            ],
            [
                'name' => 'Nusrat Sultana',
                'email' => 'nusrat.sultana@example.com',
                'phone' => '+8801711000004',
                'subject' => 'Complaint about a tutor',
                'message' => 'I am disappointed with a tutor who cancelled multiple sessions without prior notice. I request you to look into this matter and take necessary action.',
                'status' => 'open',
            ],
            [
                'name' => 'Mahbubur Rahman',
                'email' => 'mahbubur.rahman@example.com',
                'phone' => '+8801711000005',
                'subject' => 'Partnership inquiry',
                'message' => 'We are an educational institute interested in partnering with Tutor Finder for providing tutoring services to our students. Would love to discuss this further.',
                'status' => 'open',
            ],
            [
                'name' => 'Salma Khatun',
                'email' => 'salma.khatun@example.com',
                'phone' => '+8801711000006',
                'subject' => 'Question about payment',
                'message' => 'How are the tutor payments processed? Is it after every session or monthly? Please clarify the payment structure.',
                'status' => 'closed',
            ],
            [
                'name' => 'Tariq Ahmed',
                'email' => 'tariq.ahmed@example.com',
                'phone' => '+8801711000007',
                'subject' => 'Technical issue',
                'message' => 'I am unable to update my profile picture. The upload button is not working. Please help me resolve this issue.',
                'status' => 'open',
            ],
            [
                'name' => 'Farida Yasmin',
                'email' => 'farida.yasmin@example.com',
                'phone' => '+8801711000008',
                'subject' => 'Request for bulk tutoring',
                'message' => 'We are looking for tutors for a group of 10 students in Dhanmondi area for SSC preparation. Please let us know if you can arrange multiple tutors.',
                'status' => 'open',
            ],
        ];

        foreach ($messages as $message) {
            ContactMessage::create($message);
        }

        $this->command->info('Created '.count($messages).' contact messages.');
    }
}
