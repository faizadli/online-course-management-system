import { z } from 'zod';

const requiredString = (label) =>
    z.string({ required_error: `${label} is required.` }).min(1, `${label} is required.`);

const emailField = z
    .string({ required_error: 'Email address is required.' })
    .min(1, 'Email address is required.')
    .email('Enter a valid email address.')
    .max(255, 'Email must not exceed 255 characters.');

const passwordField = z
    .string({ required_error: 'Password is required.' })
    .min(8, 'Password must be at least 8 characters.');

const termsField = z
    .union([z.literal('1'), z.literal('on'), z.literal(true)])
    .optional()
    .refine((value) => value === '1' || value === 'on' || value === true, {
        message: 'You must agree to the Terms of Service and Privacy Policy.',
    });

export const validationSchemas = {
    login: z.object({
        email: emailField,
        password: requiredString('Password'),
        remember: z.any().optional(),
    }),

    'register-student': z
        .object({
            name: requiredString('Full name').max(255, 'Full name must not exceed 255 characters.'),
            email: emailField,
            password: passwordField,
            password_confirmation: requiredString('Password confirmation'),
            terms: termsField,
        })
        .refine((data) => data.password === data.password_confirmation, {
            message: 'Password confirmation does not match.',
            path: ['password_confirmation'],
        }),

    'register-instructor': z
        .object({
            name: requiredString('Full name').max(255, 'Full name must not exceed 255 characters.'),
            email: emailField,
            password: passwordField,
            password_confirmation: requiredString('Password confirmation'),
            expertise: requiredString('Area of expertise').max(255, 'Expertise must not exceed 255 characters.'),
            bio: z.string().max(1000, 'Bio must not exceed 1000 characters.').optional().or(z.literal('')),
            terms: termsField,
        })
        .refine((data) => data.password === data.password_confirmation, {
            message: 'Password confirmation does not match.',
            path: ['password_confirmation'],
        }),

    'admin-profile': z.object({
        name: requiredString('Full name').max(255, 'Full name must not exceed 255 characters.'),
        email: emailField,
        bio: z.string().max(1000, 'Bio must not exceed 1000 characters.').optional().or(z.literal('')),
    }),

    'admin-password': z
        .object({
            current_password: requiredString('Current password'),
            password: passwordField,
            password_confirmation: requiredString('Password confirmation'),
        })
        .refine((data) => data.password === data.password_confirmation, {
            message: 'Password confirmation does not match.',
            path: ['password_confirmation'],
        }),

    'category-create': z.object({
        name: requiredString('Category name').max(255, 'Category name must not exceed 255 characters.'),
        description: z.string().max(500, 'Description must not exceed 500 characters.').optional().or(z.literal('')),
    }),

    'category-edit': z.object({
        name: requiredString('Category name').max(255, 'Category name must not exceed 255 characters.'),
        description: z.string().max(500, 'Description must not exceed 500 characters.').optional().or(z.literal('')),
        is_active: z.any().optional(),
    }),

    'instructor-course-create': z.object({
        title: requiredString('Course title').max(255, 'Course title must not exceed 255 characters.'),
        category_id: z.any().optional(),
        price: z.any().optional(),
        level: z.any().optional(),
        language: z.any().optional(),
        short_description: z.string().max(500, 'Short description must not exceed 500 characters.').optional().or(z.literal('')),
        description: z.string().max(5000, 'Description must not exceed 5000 characters.').optional().or(z.literal('')),
    }),

    'instructor-course-update': z.object({
        title: requiredString('Course title').max(255, 'Course title must not exceed 255 characters.'),
        category_id: z.any().optional(),
        status: requiredString('Status'),
        price: z.any().optional(),
        level: z.any().optional(),
        language: z.any().optional(),
        short_description: z.string().max(500, 'Short description must not exceed 500 characters.').optional().or(z.literal('')),
        description: z.string().max(5000, 'Description must not exceed 5000 characters.').optional().or(z.literal('')),
    }),

    'instructor-chapter': z.object({
        title: requiredString('Chapter title').max(255, 'Chapter title must not exceed 255 characters.'),
    }),

    'instructor-content': z
        .object({
            title: requiredString('Content title').max(255, 'Content title must not exceed 255 characters.'),
            type: z.enum(['video', 'text', 'pdf'], { required_error: 'Content type is required.' }),
            body: z.string().optional().or(z.literal('')),
            url: z.string().optional().or(z.literal('')),
            duration_minutes: z.any().optional(),
        })
        .superRefine((data, ctx) => {
            if ((data.type === 'video' || data.type === 'pdf') && !data.url) {
                ctx.addIssue({ code: 'custom', message: 'URL is required for video and PDF content.', path: ['url'] });
            }
            if (data.type === 'text' && !data.body) {
                ctx.addIssue({ code: 'custom', message: 'Content body is required for text lessons.', path: ['body'] });
            }
        }),

    'instructor-assessment-create': z.object({
        title: requiredString('Assessment title').max(255, 'Assessment title must not exceed 255 characters.'),
        course_id: requiredString('Course'),
        type: z.enum(['mcq', 'essay'], { required_error: 'Assessment type is required.' }),
        description: z.string().max(1000).optional().or(z.literal('')),
        pass_score: z.any().optional(),
        time_limit_minutes: z.any().optional(),
    }),

    'instructor-question': z.object({
        question: requiredString('Question').max(2000, 'Question must not exceed 2000 characters.'),
        points: z.any().optional(),
        model_answer: z.any().optional(),
        options: z.any().optional(),
        correct_option: z.any().optional(),
    }),

    'instructor-grade': z.object({
        score: requiredString('Score'),
        feedback: z.string().max(2000).optional().or(z.literal('')),
    }),

    'instructor-profile': z.object({
        name: requiredString('Full name').max(255, 'Full name must not exceed 255 characters.'),
        email: emailField,
        expertise: z.string().max(255, 'Expertise must not exceed 255 characters.').optional().or(z.literal('')),
        bio: z.string().max(1000, 'Bio must not exceed 1000 characters.').optional().or(z.literal('')),
    }),
};
