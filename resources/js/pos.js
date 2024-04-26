import React from 'react';
import { createRoot } from 'react-dom/client';
import { MemoryRouter, Routes, Route } from 'react-router-dom';
import { SnackbarProvider } from 'notistack';
import CssBaseline from '@mui/material/CssBaseline';
import { createTheme, ThemeProvider } from '@mui/material/styles';
import Home from './pos/views/Home';
import Student from './pos/views/Student';
import StudentSelected from './pos/views/StudentSelected';

const theme = createTheme({
    palette: {
        mode: 'dark'
    }
});

function App() {
    return (
        <>
            <ThemeProvider theme={theme}>
                <CssBaseline />
                <SnackbarProvider>
                    <MemoryRouter>
                        <Routes>
                            <Route path="/" element={<Home />} />
                            <Route path="student" element={<Student />} />
                            <Route path="student/:studentId" element={<StudentSelected />} />
                        </Routes>
                    </MemoryRouter>
                </SnackbarProvider>
            </ThemeProvider>
        </>
    );

}

createRoot(document.getElementById('app')).render(<App />);
