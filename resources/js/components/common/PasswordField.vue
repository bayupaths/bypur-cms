<script setup lang="ts">
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

defineProps<{
    id?: string;
    placeholder?: string;
    disabled?: boolean;
}>();

defineOptions({ inheritAttrs: false });

const model = defineModel<string>({ default: '' });
const show = ref(false);
</script>

<template>
    <div class="relative">
        <Input
            :id="id"
            v-model="model"
            :type="show ? 'text' : 'password'"
            :placeholder="placeholder"
            :disabled="disabled"
            class="pr-10"
            v-bind="$attrs"
        />
        <Button
            type="button"
            variant="ghost"
            size="icon"
            class="absolute top-0 right-0 h-full px-3 text-muted-foreground hover:text-foreground"
            @click="show = !show"
        >
            <Eye v-if="!show" class="size-4" />
            <EyeOff v-else class="size-4" />
        </Button>
    </div>
</template>
