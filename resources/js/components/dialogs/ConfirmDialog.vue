<script setup lang="ts">
import { useConfirmDialog } from '@/composables/shared/useConfirmDialog';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const { isOpen, dialogTitle, dialogDescription, confirmText, cancelText, variant, confirm, cancel } =
    useConfirmDialog();
</script>

<template>
    <Dialog :open="isOpen" @update:open="(val) => !val && cancel()">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ dialogTitle }}</DialogTitle>
                <DialogDescription>{{ dialogDescription }}</DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="cancel">{{ cancelText }}</Button>
                <Button :variant="variant" @click="confirm">{{ confirmText }}</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
