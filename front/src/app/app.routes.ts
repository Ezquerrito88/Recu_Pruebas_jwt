import { Routes } from '@angular/router';
import { LoginComponent } from './pages/login/login';
import { ProfileComponent } from './pages/profile/profile'; 
import { authGuard } from './auth/auth-guard';

export const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  { path: 'login', component: LoginComponent },
  
  { 
    path: 'profile', 
    component: ProfileComponent, 
    canActivate: [authGuard] 
  },
];