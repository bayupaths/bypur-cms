import { useForm } from "@inertiajs/vue3";
import type { InertiaForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import type { ZodSchema, ZodError } from "zod";

type FormDataKeys<T> =
    T extends Record<string, unknown> ? keyof T & string : never;

export function useFormValidation<T extends Record<string, unknown>>(
    initialData: T,
    schema?: ZodSchema<T>,
) {
    const form = (useForm as unknown as (data: T) => InertiaForm<T>)(
        initialData,
    );
    const zodErrors = ref<Partial<Record<FormDataKeys<T>, string>>>({});

    const hasErrors = computed(
        () =>
            Object.keys(form.errors).length > 0 ||
            Object.keys(zodErrors.value).length > 0,
    );

    function getError(field: FormDataKeys<T>): string | undefined {
        return (
            zodErrors.value[field] ??
            (form.errors as Record<string, string | undefined>)[field]
        );
    }

    function clearError(field: FormDataKeys<T>) {
        delete zodErrors.value[field];
        (form.clearErrors as (field: string) => void)(field);
    }

    function validate(): boolean {
        if (!schema) return true;

        zodErrors.value = {};

        const result = schema.safeParse(form.data());

        if (!result.success) {
            const fieldErrors = (result.error as ZodError).flatten()
                .fieldErrors as Record<string, string[] | undefined>;
            for (const key in fieldErrors) {
                const messages = fieldErrors[key];
                if (messages?.[0]) {
                    zodErrors.value[key as FormDataKeys<T>] = messages[0];
                }
            }
            return false;
        }

        return true;
    }

    function handleSubmit(callback: (form: InertiaForm<T>) => void) {
        if (validate()) {
            callback(form);
        }
    }

    return {
        form,
        hasErrors,
        zodErrors,
        getError,
        clearError,
        validate,
        handleSubmit,
    };
}
