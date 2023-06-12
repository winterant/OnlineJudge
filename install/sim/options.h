/*	This file is part of the software similarity tester SIM.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: options.h,v 1.19 2020-08-14 16:56:51 dick Exp $
*/

/*	Setting and consulting command line options */

/* Command-line parameters */
extern int Min_Run_Size;
extern int Page_Width;
extern int Threshold_Percentage;
extern const char *Output_Name;

extern int Is_Set_Option(int ch);

#ifdef	_OPTIONS_A_
extern int Get_Options(int argc, const char *argv[]);
extern void Show_All_Options(void);
extern void Show_Actual_Options(void);
#endif	/*_OPTIONS_A_*/
