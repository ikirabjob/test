<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Error alert -->
    <div v-if="error" class="bg-red-100 text-red-700 p-4 rounded mb-6">
      {{ error }}
      <button @click="error = null" class="ml-4 text-sm font-bold">
        Dismiss
      </button>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Events</h1>
      <button
          v-if="currentUser"
          @click="handleCreateEvent"
          class="bg-blue-600 text-white px-4 py-2 rounded"
      >
        Create Event
      </button>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center p-8">Loading events...</div>

    <!-- Event list -->
    <div v-else-if="events.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
          v-for="event in events"
          :key="event.id"
          class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow"
      >
        <div class="p-4">
          <h2 class="text-xl font-bold mb-2">{{ event.title }}</h2>
          <p class="text-gray-600 mb-4 line-clamp-2">{{ event.description || 'No description' }}</p>
          <div class="text-sm text-gray-500 mb-4">
            <div class="mb-1">
              <span class="font-medium">Location:</span> {{ event.location || 'TBD' }}
            </div>
            <div class="mb-1">
              <span class="font-medium">Start:</span> {{ formatDate(event.start_date) }}
            </div>
            <div class="mb-1">
              <span class="font-medium">End:</span> {{ formatDate(event.end_date) }}
            </div>
            <div v-if="event.capacity" class="mb-1">
              <span class="font-medium">Capacity:</span> {{ event.registration_count || 0 }}/{{ event.capacity }}
            </div>
          </div>
          <div class="flex justify-between items-center">
            <div v-if="currentUser">
              <div v-if="isEventCreator(event)" class="flex gap-2">
                <button
                    @click="handleEditEvent(event)"
                    class="bg-gray-200 px-3 py-1 rounded text-sm"
                >
                  Edit
                </button>
                <button
                    @click="handleDeleteEvent(event.id)"
                    class="bg-red-100 text-red-600 px-3 py-1 rounded text-sm"
                >
                  Delete
                </button>
              </div>
              <div v-else>
                <button
                    @click="handleRegister(event.id)"
                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm"
                    :disabled="isEventFull(event)"
                >
                  Register
                </button>
              </div>
            </div>
            <div v-else>
              <button
                  @click="$router.push('/login')"
                  class="bg-gray-200 px-3 py-1 rounded text-sm"
              >
                Login to Register
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="text-center py-8">
      <p class="text-lg text-gray-600">No events found</p>
    </div>

    <!-- Event Form Modal -->
    <teleport to="body">
      <div v-if="showEventForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full">
          <h2 class="text-2xl font-bold mb-4">
            {{ selectedEvent ? 'Edit Event' : 'Create Event' }}
          </h2>
          <form @submit.prevent="handleFormSubmit">
            <div class="mb-4">
              <label class="block mb-1">Title</label>
              <input
                  v-model="formData.title"
                  type="text"
                  class="w-full border rounded p-2"
                  required
              />
            </div>
            <div class="mb-4">
              <label class="block mb-1">Description</label>
              <textarea
                  v-model="formData.description"
                  class="w-full border rounded p-2"
                  rows="4"
              ></textarea>
            </div>
            <div class="mb-4">
              <label class="block mb-1">Location</label>
              <input
                  v-model="formData.location"
                  type="text"
                  class="w-full border rounded p-2"
              />
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block mb-1">Start Date</label>
                <input
                    v-model="formData.start_date"
                    type="datetime-local"
                    class="w-full border rounded p-2"
                    required
                />
              </div>
              <div>
                <label class="block mb-1">End Date</label>
                <input
                    v-model="formData.end_date"
                    type="datetime-local"
                    class="w-full border rounded p-2"
                    required
                />
              </div>
            </div>
            <div class="mb-4">
              <label class="block mb-1">Capacity</label>
              <input
                  v-model="formData.capacity"
                  type="number"
                  class="w-full border rounded p-2"
                  min="1"
              />
            </div>
            <div class="mb-4">
              <label class="flex items-center">
                <input
                    v-model="formData.is_public"
                    type="checkbox"
                    class="mr-2"
                />
                Public Event
              </label>
            </div>
            <div class="flex justify-end gap-2">
              <button
                  type="button"
                  @click="showEventForm = false"
                  class="border px-4 py-2 rounded"
              >
                Cancel
              </button>
              <button
                  type="submit"
                  class="bg-blue-600 text-white px-4 py-2 rounded"
              >
                {{ selectedEvent ? 'Update Event' : 'Create Event' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import authService from '../services/auth_service';
import eventService from '../services/event_service';

const router = useRouter();
const events = ref([]);
const loading = ref(true);
const error = ref(null);
const currentUser = ref(null);
const selectedEvent = ref(null);
const showEventForm = ref(false);
const formData = reactive({
  title: '',
  description: '',
  location: '',
  start_date: '',
  end_date: '',
  capacity: '',
  is_public: true,
  categories: []
});

onMounted(async () => {
  try {
    loading.value = true;
    events.value = await eventService.getEvents(20, 0);

    if (authService.isAuthenticated()) {
      currentUser.value = await authService.getCurrentUser();
    }
  } catch (err) {
    error.value = err.message || 'Failed to load events';
  } finally {
    loading.value = false;
  }
});

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString();
};

const isEventCreator = (event) => {
  return currentUser.value && event.creator_id === currentUser.value.id;
};

const isEventFull = (event) => {
  return event.capacity !== null && (event.registration_count || 0) >= event.capacity;
};

const handleRegister = async (eventId) => {
  try {
    await eventService.registerForEvent(eventId);
    // Refresh events to update registration count
    events.value = await eventService.getEvents(20, 0);
  } catch (err) {
    error.value = err.message || 'Failed to register for event';
  }
};

const handleCancelRegistration = async (eventId) => {
  try {
    await eventService.cancelRegistration(eventId);
    // Refresh events to update registration count
    events.value = await eventService.getEvents(20, 0);
  } catch (err) {
    error.value = err.message || 'Failed to cancel registration';
  }
};

const handleDeleteEvent = async (eventId) => {
  if (window.confirm('Are you sure you want to delete this event?')) {
    try {
      await eventService.deleteEvent(eventId);
      // Remove the deleted event from the state
      events.value = events.value.filter(event => event.id !== eventId);
    } catch (err) {
      error.value = err.message || 'Failed to delete event';
    }
  }
};

const handleCreateEvent = () => {
  selectedEvent.value = null;
  Object.assign(formData, {
    title: '',
    description: '',
    location: '',
    start_date: '',
    end_date: '',
    capacity: '',
    is_public: true,
    categories: []
  });
  showEventForm.value = true;
};

const handleEditEvent = (event) => {
  selectedEvent.value = event;
  Object.assign(formData, {
    title: event.title,
    description: event.description || '',
    location: event.location || '',
    start_date: event.start_date.substring(0, 16),
    end_date: event.end_date.substring(0, 16),
    capacity: event.capacity ? String(event.capacity) : '',
    is_public: event.is_public,
    categories: []
  });
  showEventForm.value = true;
};

const handleFormSubmit = async () => {
  try {
    const eventData = {
      ...formData,
      capacity: formData.capacity ? parseInt(formData.capacity) : null,
      categories: []
    };

    if (selectedEvent.value) {
      await eventService.updateEvent(selectedEvent.value.id, eventData);
    } else {
      await eventService.createEvent(eventData);
    }

    // Refresh events
    events.value = await eventService.getEvents(20, 0);
    showEventForm.value = false;
  } catch (err) {
    error.value = err.message || 'Failed to save event';
  }
};
</script>