import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../auth/auth';
import { Router } from '@angular/router';
import { User } from '../../auth/auth.model';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './profile.html',
  styleUrl: './profile.css'
})
export class ProfileComponent {
  user: User | null = null;

  constructor(private auth: AuthService, private router: Router) {
    this.auth.getProfile().subscribe({
      next: (data) => this.user = data,
      error: (err) => console.error('Error cargando perfil', err)
    });
  }

  logout() {
    this.auth.logout().subscribe(() => {
      this.router.navigate(['/login']);
    });
  }
}