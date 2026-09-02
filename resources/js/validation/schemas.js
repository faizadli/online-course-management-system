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
};
