import api from './api';
import { Attendee, Event, EventFormData } from '../types';

class EventService {
    async getEvents(limit: number = 10, offset: number = 0): Promise<Event[]> {
        return api.get<Event[]>(`/events?limit=${limit}&offset=${offset}`);
    }

    async getEvent(id: number): Promise<Event> {
        return api.get<Event>(`/events/${id}`);
    }

    async createEvent(eventData: EventFormData): Promise<Event> {
        return api.post<Event>('/events', eventData);
    }

    async updateEvent(id: number, eventData: Partial<EventFormData>): Promise<Event> {
        return api.put<Event>(`/events/${id}`, eventData);
    }

    async deleteEvent(id: number): Promise<{ message: string }> {
        return api.delete<{ message: string }>(`/events/${id}`);
    }

    async registerForEvent(eventId: number): Promise<{ message: string }> {
        return api.post<{ message: string }>(`/events/${eventId}/register`);
    }

    async cancelRegistration(eventId: number): Promise<{ message: string }> {
        return api.post<{ message: string }>(`/events/${eventId}/cancel`);
    }

    async getEventAttendees(eventId: number): Promise<Attendee[]> {
        return api.get<Attendee[]>(`/events/${eventId}/attendees`);
    }
}

export default new EventService();