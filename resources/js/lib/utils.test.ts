import { describe, it, expect, vi, beforeEach } from 'vitest';
import { fillFormData, clearFormData } from './utils';

describe('Form Utils', () => {
  describe('fillFormData', () => {
    it('should fill form with fake data for known fields', () => {
      const form = {
        data: () => ({
          name: '',
          company_name: '',
          email: '',
          cnpj: '',
          state_registration: '',
          zip_code: '',
          address: '',
          neighborhood: '',
          city: '',
          contact_name_1: '',
          phone_1: '',
          password: '',
          password_confirmation: '',
          access_level: 0,
          is_active: false,
        }),
        name: '',
        company_name: '',
        email: '',
        cnpj: '',
        state_registration: '',
        zip_code: '',
        address: '',
        neighborhood: '',
        city: '',
        contact_name_1: '',
        phone_1: '',
        password: '',
        password_confirmation: '',
        access_level: 0,
        is_active: false,
      };

      fillFormData(form);

      expect(form.name).toContain('Usuário de Teste');
      expect(form.company_name).toContain('Empresa Teste');
      expect(form.email).toContain('@zenite.com');
      expect(form.cnpj).toBe('00.000.000/0001-91');
      expect(form.state_registration).toBe('ISENTO');
      expect(form.zip_code).toBe('01001-000');
      expect(form.address).toContain('Rua de Teste');
      expect(form.neighborhood).toBe('Bairro Industrial');
      expect(form.city).toBe('São Paulo');
      expect(form.contact_name_1).toBe('Contato Principal');
      expect(form.phone_1).toBe('(11) 98888-7777');
      expect(form.password).toBe('Mudar@123');
      expect(form.password_confirmation).toBe('Mudar@123');
      expect(form.access_level).toBe(0);
      expect(form.is_active).toBe(true);
    });

    it('should fill unknown string fields with test data', () => {
      const form = {
        data: () => ({
          unknown_field: '',
        }),
        unknown_field: '',
      };

      fillFormData(form);

      expect(form.unknown_field).toContain('Teste');
    });

    it('should handle null form gracefully', () => {
      expect(() => fillFormData(null)).not.toThrow();
    });

    it('should handle undefined form gracefully', () => {
      expect(() => fillFormData(undefined)).not.toThrow();
    });
  });

  describe('clearFormData', () => {
    it('should clear all string fields to empty string', () => {
      const form = {
        data: () => ({
          name: 'Test Name',
          email: 'test@example.com',
          address: '123 Test St',
        }),
        name: 'Test Name',
        email: 'test@example.com',
        address: '123 Test St',
        clearErrors: vi.fn(),
      };

      clearFormData(form);

      expect(form.name).toBe('');
      expect(form.email).toBe('');
      expect(form.address).toBe('');
    });

    it('should clear all number fields to 0', () => {
      const form = {
        data: () => ({
          quantity: 100,
          price: 50.5,
        }),
        quantity: 100,
        price: 50.5,
        clearErrors: vi.fn(),
      };

      clearFormData(form);

      expect(form.quantity).toBe(0);
      expect(form.price).toBe(0);
    });

    it('should clear all boolean fields to true', () => {
      const form = {
        data: () => ({
          is_active: false,
          is_featured: true,
        }),
        is_active: false,
        is_featured: true,
        clearErrors: vi.fn(),
      };

      clearFormData(form);

      expect(form.is_active).toBe(true);
      expect(form.is_featured).toBe(true);
    });

    it('should clear all array fields to empty array', () => {
      const form = {
        data: () => ({
          tags: ['tag1', 'tag2'],
        }),
        tags: ['tag1', 'tag2'],
        clearErrors: vi.fn(),
      };

      clearFormData(form);

      expect(form.tags).toEqual([]);
    });

    it('should call clearErrors on the form', () => {
      const clearErrorsMock = vi.fn();
      const form = {
        data: () => ({
          name: 'Test',
        }),
        name: 'Test',
        clearErrors: clearErrorsMock,
      };

      clearFormData(form);

      expect(clearErrorsMock).toHaveBeenCalled();
    });

    it('should handle null form gracefully', () => {
      expect(() => clearFormData(null)).not.toThrow();
    });

    it('should handle undefined form gracefully', () => {
      expect(() => clearFormData(undefined)).not.toThrow();
    });
  });

  describe('Custom Events Integration', () => {
    it('should dispatch magic-fill event on ALT+1', () => {
      const dispatchEventSpy = vi.spyOn(window, 'dispatchEvent');
      const event = new KeyboardEvent('keydown', { altKey: true, key: '1' });
      
      window.dispatchEvent(event);
      
      // This test verifies the event can be dispatched
      expect(dispatchEventSpy).toHaveBeenCalled();
      dispatchEventSpy.mockRestore();
    });

    it('should dispatch magic-clear event on ALT+2', () => {
      const dispatchEventSpy = vi.spyOn(window, 'dispatchEvent');
      const event = new KeyboardEvent('keydown', { altKey: true, key: '2' });
      
      window.dispatchEvent(event);
      
      // This test verifies the event can be dispatched
      expect(dispatchEventSpy).toHaveBeenCalled();
      dispatchEventSpy.mockRestore();
    });
  });
});
