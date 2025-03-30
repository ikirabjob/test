-- Create users table
CREATE TABLE users (
       id SERIAL PRIMARY KEY,
       email VARCHAR(255) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL,
       first_name VARCHAR(100) NOT NULL,
       last_name VARCHAR(100) NOT NULL,
       role VARCHAR(20) NOT NULL DEFAULT 'user',
       created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create events table
CREATE TABLE events (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        location VARCHAR(255),
        start_date TIMESTAMP WITH TIME ZONE NOT NULL,
        end_date TIMESTAMP WITH TIME ZONE NOT NULL,
        capacity INTEGER,
        is_public BOOLEAN DEFAULT TRUE,
        creator_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create event registrations table
CREATE TABLE event_registrations (
         id SERIAL PRIMARY KEY,
         event_id INTEGER REFERENCES events(id) ON DELETE CASCADE,
         user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
         registration_date TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
         status VARCHAR(20) DEFAULT 'registered', -- registered, cancelled, attended
         UNIQUE (event_id, user_id)
);

-- Create categories table
CREATE TABLE categories (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT
);

-- Create event_categories (many-to-many relationship)
CREATE TABLE event_categories (
          event_id INTEGER REFERENCES events(id) ON DELETE CASCADE,
          category_id INTEGER REFERENCES categories(id) ON DELETE CASCADE,
          PRIMARY KEY (event_id, category_id)
);

-- Create notifications table
CREATE TABLE notifications (
           id SERIAL PRIMARY KEY,
           user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
           title VARCHAR(255) NOT NULL,
           message TEXT NOT NULL,
           is_read BOOLEAN DEFAULT FALSE,
           created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create password reset tokens
CREATE TABLE password_reset_tokens (
           id SERIAL PRIMARY KEY,
           user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
           token VARCHAR(100) NOT NULL,
           expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
           created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create functions and triggers for updated_at
CREATE OR REPLACE FUNCTION update_modified_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

-- Create trigger for users table
CREATE TRIGGER update_users_modtime
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_modified_column();

-- Create trigger for events table
CREATE TRIGGER update_events_modtime
    BEFORE UPDATE ON events
    FOR EACH ROW
    EXECUTE FUNCTION update_modified_column();

-- Create indexes for better performance
CREATE INDEX idx_events_start_date ON events(start_date);
CREATE INDEX idx_events_creator ON events(creator_id);
CREATE INDEX idx_event_registrations_event ON event_registrations(event_id);
CREATE INDEX idx_event_registrations_user ON event_registrations(user_id);
CREATE INDEX idx_notifications_user ON notifications(user_id);