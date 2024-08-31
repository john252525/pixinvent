<template>
  <div class="h-100 d-flex align-center justify-md-space-between justify-center">
    <!-- 👉 Footer: left content -->
    <span class="d-flex align-center text-medium-emphasis">
      &copy;
      {{ new Date().getFullYear() }}
      Сделано с
      <VIcon
        icon="emojione-v1:red-heart"
        size="1.25rem"
        class="mx-1"
      />
    </span>
    <!-- 👉 Footer: right content -->
    <span class="d-md-flex gap-x-4 text-primary d-none">
      <a
        href="#"
        target="noopener noreferrer"
      >link</a>
      <a
        href="#"
        target="noopener noreferrer"
      >link</a>
      <a
        href="#"
        target="noopener noreferrer"
      >link</a>
      <a
        href="#"
        target="noopener noreferrer"
      >link</a>
    </span>
  </div>
</template>
